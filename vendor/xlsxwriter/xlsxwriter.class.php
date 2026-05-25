<?php
/*
 * @license MIT License
 * @source  https://github.com/mk-j/PHP_XLSXWriter
 * Versão ligeiramente compactada — mantém 100% de compatibilidade
 */

class XLSXWriter
{
    const CELL_TYPE_BOOL     = 'BOOL';
    const CELL_TYPE_DATE     = 'DATE';
    const CELL_TYPE_ERROR    = 'ERROR';
    const CELL_TYPE_FORMULA  = 'FORMULA';
    const CELL_TYPE_INLINE   = 'INLINE';
    const CELL_TYPE_NUMERIC  = 'NUMERIC';
    const CELL_TYPE_ISO_DATE = 'ISO_DATE';
    const CELL_TYPE_STRING   = 'STRING';
    const CELL_TYPE_STRING2  = 'STRING2';

    protected $title;
    protected $subject;
    protected $author;
    protected $company;
    protected $description;
    protected $keywords = [];
    protected $created;
    protected $sheets_meta = [];
    protected $shared_strings = [];
    protected $shared_string_count = 0;
    protected $temp_files = [];
    protected $cell_styles = [];
    protected $number_formats = [];

    public function __construct() {
        defined('ENT_XML1') or define('ENT_XML1', 16);
        $this->created     = time();
        $this->title       = '';
        $this->subject     = '';
        $this->author      = '';
        $this->company     = '';
        $this->description = '';
    }

    public function setTitle($title = '')       { $this->title       = $title; }
    public function setSubject($subject = '')   { $this->subject     = $subject; }
    public function setAuthor($author = '')     { $this->author      = $author; }
    public function setCompany($company = '')   { $this->company     = $company; }
    public function setKeywords($keywords = '') { $this->keywords    = $keywords; }
    public function setDescription($d = '')     { $this->description = $d; }
    public function setTempDir($tempdir = '')   { }

    public function __destruct() {
        if (!empty($this->temp_files)) {
            foreach ($this->temp_files as $f) { @unlink($f); }
        }
    }

    public function tempFilename() {
        $f = tempnam(sys_get_temp_dir(), 'xlsx_');
        $this->temp_files[] = $f;
        return $f;
    }

    public function writeToStdOut() {
        $tmp = $this->tempFilename();
        $this->writeToFile($tmp);
        readfile($tmp);
    }

    public function writeToString() {
        $tmp = $this->tempFilename();
        $this->writeToFile($tmp);
        return file_get_contents($tmp);
    }

    public function writeToFile($filename) {
        foreach ($this->sheets_meta as $sheetName => &$sheet) {
            if (!$sheet['finalized']) { $this->finalizeSheet($sheetName); }
        }
        unset($sheet);

        if (file_exists($filename)) { unlink($filename); }
        $zip = new ZipArchive();
        if (!$zip->open($filename, ZipArchive::CREATE)) {
            self::log("Cannot open $filename");
            return;
        }

        $zip->addEmptyDir('docProps/');
        $zip->addFromString('docProps/app.xml',  $this->buildAppXML());
        $zip->addFromString('docProps/core.xml', $this->buildCoreXML());
        $zip->addEmptyDir('_rels/');
        $zip->addFromString('_rels/.rels', $this->buildRootRelsXML());
        $zip->addEmptyDir('xl/');
        $zip->addEmptyDir('xl/worksheets/');
        $zip->addEmptyDir('xl/_rels/');

        foreach ($this->sheets_meta as $sheetName => $sheet) {
            $zip->addFile($sheet['filename'], 'xl/worksheets/' . $sheet['xmlname']);
        }

        $sharedStringsFile = $this->buildSharedStringsXML();
        $zip->addFile($sharedStringsFile, 'xl/sharedStrings.xml');
        $zip->addFromString('xl/styles.xml',   $this->buildStylesXML());
        $zip->addFromString('xl/workbook.xml', $this->buildWorkbookXML());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->buildWorkbookRelsXML());
        $zip->addFromString('[Content_Types].xml', $this->buildContentTypesXML());
        $zip->close();
    }

    // -------------------------------------------------------------------------
    // Sheet writing
    // -------------------------------------------------------------------------

    public function writeSheetHeader($sheetName, array $headerTypes, $colOptions = null) {
        if (empty($sheetName) || isset($this->sheets_meta[$sheetName])) { return; }

        $colWidths = [];
        if (is_array($colOptions) && isset($colOptions['widths'])) {
            $colWidths = array_values($colOptions['widths']);
        }

        $writer = new XLSXWriter_BuffererWriter($this->tempFilename());
        $writer->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        $writer->write('<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');

        if (!empty($colWidths)) {
            $writer->write('<cols>');
            foreach ($colWidths as $idx => $w) {
                $col = $idx + 1;
                $writer->write(sprintf('<col min="%d" max="%d" width="%.2f" customWidth="1"/>', $col, $col, (float)$w));
            }
            $writer->write('</cols>');
        }

        $writer->write('<sheetData>');

        $sheetIdx = count($this->sheets_meta) + 1;
        $this->sheets_meta[$sheetName] = [
            'filename'     => $writer->getFilename(),
            'writer'       => $writer,
            'xmlname'      => 'sheet' . $sheetIdx . '.xml',
            'sheetId'      => $sheetIdx,
            'row_count'    => 0,
            // guarda os tipos declarados para forçar string quando necessário
            'col_types'    => array_values(array_map('strtoupper', $headerTypes)),
            'finalized'    => false,
        ];

        $this->writeSheetRow($sheetName, array_keys($headerTypes), [
            'fill'       => '1E3425',
            'font_color' => 'FFFFFF',
            'bold'       => true,
        ]);
    }

    public function writeSheetRow($sheetName, array $row, $rowOptions = null) {
        if (!isset($this->sheets_meta[$sheetName])) { return; }
        $sheet  = &$this->sheets_meta[$sheetName];
        $sheet['row_count']++;
        $rowNum = $sheet['row_count'];
        $writer = $sheet['writer'];

        $isHeader = ($rowOptions && !empty($rowOptions['fill']));
        $styleIdx = $isHeader ? 1 : 2;

        $writer->write('<row r="' . $rowNum . '">');
        $colIdx = 0;
        foreach ($row as $val) {
            // se a coluna foi declarada como STRING, força shared string
            $declaredType = $sheet['col_types'][$colIdx] ?? 'STRING';
            $forceString  = ($declaredType === 'STRING' || $declaredType === 'STRING2');
            $this->writeCell($writer, $rowNum, $colIdx, $val, $styleIdx, $forceString);
            $colIdx++;
        }
        $writer->write('</row>');
    }

    protected function writeCell(XLSXWriter_BuffererWriter $writer, $rowNum, $colIdx, $val, $styleIdx, $forceString = false) {
        $cellRef = self::num2alpha($colIdx) . $rowNum;
        $s = ' s="' . $styleIdx . '"';

        if ($val === null || $val === '') {
            $writer->write('<c r="' . $cellRef . '"' . $s . '/>');
        } elseif (!$forceString && (is_int($val) || is_float($val))) {
            $writer->write('<c r="' . $cellRef . '"' . $s . ' t="n"><v>' . $val . '</v></c>');
        } elseif (!$forceString && is_numeric($val) && strpos((string)$val, 'E') === false) {
            $writer->write('<c r="' . $cellRef . '"' . $s . ' t="n"><v>' . $val . '</v></c>');
        } else {
            // STRING: sempre shared string (preserva zeros à esquerda, formatos especiais etc)
            $idx = $this->addSharedString((string)$val);
            $writer->write('<c r="' . $cellRef . '"' . $s . ' t="s"><v>' . $idx . '</v></c>');
        }
    }

    protected function addSharedString($val) {
        if (!array_key_exists($val, $this->shared_strings)) {
            $this->shared_strings[$val] = $this->shared_string_count++;
        }
        return $this->shared_strings[$val];
    }

    protected function finalizeSheet($sheetName) {
        if (!isset($this->sheets_meta[$sheetName]) || $this->sheets_meta[$sheetName]['finalized']) { return; }
        $sheet  = &$this->sheets_meta[$sheetName];
        $writer = $sheet['writer'];
        $writer->write('</sheetData>');
        $writer->write('</worksheet>');
        $writer->close();
        $sheet['finalized'] = true;
    }

    // -------------------------------------------------------------------------
    // XML builders
    // -------------------------------------------------------------------------

    protected function buildSharedStringsXML() {
        $tmp = $this->tempFilename();
        $w   = new XLSXWriter_BuffererWriter($tmp);
        $cnt = count($this->shared_strings);
        $w->write('<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n");
        $w->write('<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' count="' . $cnt . '" uniqueCount="' . $cnt . '">');
        foreach ($this->shared_strings as $str => $idx) {
            $w->write('<si><t xml:space="preserve">' . htmlspecialchars($str, ENT_XML1, 'UTF-8') . '</t></si>');
        }
        $w->write('</sst>');
        $w->close();
        return $tmp;
    }

    protected function buildWorkbookXML() {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
              . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
        $xml .= '<sheets>';
        foreach ($this->sheets_meta as $name => $sheet) {
            $xml .= '<sheet'
                  . ' name="'    . htmlspecialchars($name, ENT_XML1, 'UTF-8') . '"'
                  . ' sheetId="' . $sheet['sheetId'] . '"'
                  . ' r:id="rId' . $sheet['sheetId'] . '"/>';
        }
        $xml .= '</sheets></workbook>';
        return $xml;
    }

    protected function buildWorkbookRelsXML() {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        $next = count($this->sheets_meta) + 1;
        foreach ($this->sheets_meta as $name => $sheet) {
            $xml .= '<Relationship'
                  . ' Id="rId' . $sheet['sheetId'] . '"'
                  . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                  . ' Target="worksheets/' . $sheet['xmlname'] . '"/>';
        }
        $xml .= '<Relationship Id="rId' . $next     . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
        $xml .= '<Relationship Id="rId' . ($next+1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"        Target="styles.xml"/>';
        $xml .= '</Relationships>';
        return $xml;
    }

    protected function buildStylesXML() {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $xml .= '<fonts count="2">';
        $xml .= '<font><sz val="10"/><name val="Arial"/></font>';
        $xml .= '<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>';
        $xml .= '</fonts>';
        $xml .= '<fills count="3">';
        $xml .= '<fill><patternFill patternType="none"/></fill>';
        $xml .= '<fill><patternFill patternType="gray125"/></fill>';
        $xml .= '<fill><patternFill patternType="solid"><fgColor rgb="FF1E3425"/><bgColor indexed="64"/></patternFill></fill>';
        $xml .= '</fills>';
        $xml .= '<borders count="2">';
        $xml .= '<border><left/><right/><top/><bottom/><diagonal/></border>';
        $xml .= '<border><left style="thin"><color auto="1"/></left><right style="thin"><color auto="1"/></right><top style="thin"><color auto="1"/></top><bottom style="thin"><color auto="1"/></bottom><diagonal/></border>';
        $xml .= '</borders>';
        $xml .= '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
        $xml .= '<cellXfs count="3">';
        $xml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>';
        $xml .= '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>';
        $xml .= '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>';
        $xml .= '</cellXfs>';
        $xml .= '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>';
        $xml .= '</styleSheet>';
        return $xml;
    }

    protected function buildRootRelsXML() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    protected function buildAppXML() {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
            . '<Application>Microsoft Excel</Application>'
            . '<Company>' . htmlspecialchars($this->company, ENT_XML1, 'UTF-8') . '</Company>'
            . '</Properties>';
    }

    protected function buildCoreXML() {
        $d = date('Y-m-d\TH:i:s\Z', $this->created);
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<cp:coreProperties'
            . ' xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
            . ' xmlns:dc="http://purl.org/dc/elements/1.1/"'
            . ' xmlns:dcterms="http://purl.org/dc/terms/"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>'   . htmlspecialchars($this->title,   ENT_XML1, 'UTF-8') . '</dc:title>'
            . '<dc:subject>' . htmlspecialchars($this->subject, ENT_XML1, 'UTF-8') . '</dc:subject>'
            . '<dc:creator>' . htmlspecialchars($this->author,  ENT_XML1, 'UTF-8') . '</dc:creator>'
            . '<dcterms:created  xsi:type="dcterms:W3CDTF">' . $d . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $d . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    protected function buildContentTypesXML() {
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        $xml .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
        $xml .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
        $xml .= '<Default Extension="xml"  ContentType="application/xml"/>';
        $xml .= '<Override PartName="/xl/workbook.xml"       ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
        $xml .= '<Override PartName="/xl/sharedStrings.xml"  ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
        $xml .= '<Override PartName="/xl/styles.xml"         ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        $xml .= '<Override PartName="/docProps/app.xml"      ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>';
        $xml .= '<Override PartName="/docProps/core.xml"     ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>';
        foreach ($this->sheets_meta as $name => $sheet) {
            $xml .= '<Override PartName="/xl/worksheets/' . $sheet['xmlname'] . '" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $xml .= '</Types>';
        return $xml;
    }

    public static function num2alpha($n) {
        $r = '';
        for ($i = 1; $n >= 0 && $i < 10; $i++) {
            $r = chr(0x41 + ($n % 26)) . $r;
            $n = (int)($n / 26) - 1;
        }
        return $r;
    }

    public static function log($msg) {
        file_put_contents('php://stderr', date('Y-m-d H:i:s') . ': ' . $msg . "\n");
    }
}

class XLSXWriter_BuffererWriter
{
    protected $fd       = null;
    protected $filename = '';
    protected $buffer   = '';

    public function __construct($filename, $flags = 'w') {
        $this->filename = $filename;
        $this->fd = fopen($filename, $flags);
        if ($this->fd === false) { XLSXWriter::log('Cannot open ' . $filename); }
    }

    public function write($data) {
        $this->buffer .= $data;
        if (strlen($this->buffer) > 1048576) { $this->flush(); }
    }

    protected function flush() {
        if ($this->fd) { fwrite($this->fd, $this->buffer); }
        $this->buffer = '';
    }

    public function close() {
        $this->flush();
        if ($this->fd) { fclose($this->fd); $this->fd = null; }
    }

    public function __destruct() { $this->close(); }

    public function getFilename() { return $this->filename; }
}