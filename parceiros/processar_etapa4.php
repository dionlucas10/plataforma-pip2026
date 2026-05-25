<?php
session_start();

$config = require __DIR__ . '/../app/config/db.php';
$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
    $config['user'],
    $config['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

if (!isset($_SESSION['parceiro_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$parceiro_id = $_SESSION['parceiro_id'];

// Arrays de seleção múltipla
$ods                = $_POST['ods']                ?? [];
$eixos              = $_POST['eixos']              ?? [];
$maturidade         = $_POST['maturidade']         ?? [];
$setores            = $_POST['setores']            ?? [];
$perfil_impacto     = $_POST['perfil_impacto']     ?? [];
$perfil_iniciativa  = $_POST['perfil_iniciativa']  ?? [];

// Campos abertos ("Outro")
$perfil_impacto_outro       = trim($_POST['perfil_impacto_outro']       ?? '');
$perfil_iniciativa_outro    = trim($_POST['perfil_iniciativa_outro']    ?? '');
$setor_outro_setor_primario   = trim($_POST['setor_outro_setor_primário']   ?? '');
$setor_outro_setor_secundario = trim($_POST['setor_outro_setor_secundário'] ?? '');
$setor_outro_setor_terciario  = trim($_POST['setor_outro_setor_terciário']  ?? '');

// Alcance (radio)
$alcance = trim($_POST['alcance'] ?? '');

// Validação básica
if (empty($alcance)) {
    $_SESSION['erro_etapa4'] = "Por favor, selecione o Alcance do Impacto.";
    header("Location: etapa4_interesses.php");
    exit;
}

// Codifica arrays para JSON
$eixos_json             = json_encode($eixos,             JSON_UNESCAPED_UNICODE);
$maturidade_json        = json_encode($maturidade,        JSON_UNESCAPED_UNICODE);
$setores_json           = json_encode($setores,           JSON_UNESCAPED_UNICODE);
$perfil_impacto_json    = json_encode($perfil_impacto,    JSON_UNESCAPED_UNICODE);
$perfil_iniciativa_json = json_encode($perfil_iniciativa, JSON_UNESCAPED_UNICODE);

try {
    $pdo->beginTransaction();

    // 1. Salva parceiro_interesses
    $sql_int = "
        INSERT INTO parceiro_interesses (
            parceiro_id,
            eixos_interesse,
            maturidade_negocios,
            setores_interesse,
            perfil_impacto,
            perfil_iniciativa,
            perfil_impacto_outro,
            perfil_iniciativa_outro,
            setor_outro_setor_primario,
            setor_outro_setor_secundario,
            setor_outro_setor_terciario,
            alcance_impacto
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            eixos_interesse              = VALUES(eixos_interesse),
            maturidade_negocios          = VALUES(maturidade_negocios),
            setores_interesse            = VALUES(setores_interesse),
            perfil_impacto               = VALUES(perfil_impacto),
            perfil_iniciativa            = VALUES(perfil_iniciativa),
            perfil_impacto_outro         = VALUES(perfil_impacto_outro),
            perfil_iniciativa_outro      = VALUES(perfil_iniciativa_outro),
            setor_outro_setor_primario   = VALUES(setor_outro_setor_primario),
            setor_outro_setor_secundario = VALUES(setor_outro_setor_secundario),
            setor_outro_setor_terciario  = VALUES(setor_outro_setor_terciario),
            alcance_impacto              = VALUES(alcance_impacto)
    ";

    $stmt = $pdo->prepare($sql_int);
    $stmt->execute([
        $parceiro_id,
        $eixos_json,
        $maturidade_json,
        $setores_json,
        $perfil_impacto_json,
        $perfil_iniciativa_json,
        $perfil_impacto_outro,
        $perfil_iniciativa_outro,
        $setor_outro_setor_primario,
        $setor_outro_setor_secundario,
        $setor_outro_setor_terciario,
        $alcance,
    ]);

    // 2. ODS: apaga as antigas e insere as novas
    $pdo->prepare("DELETE FROM parceiro_ods WHERE parceiro_id = ?")->execute([$parceiro_id]);

    if (!empty($ods)) {
        $stmt_ods = $pdo->prepare("INSERT INTO parceiro_ods (parceiro_id, ods_id) VALUES (?, ?)");
        foreach ($ods as $ods_id) {
            $stmt_ods->execute([$parceiro_id, (int)$ods_id]);
        }
    }

    // 3. Atualiza progresso
    $pdo->prepare("UPDATE parceiros SET etapa_atual = GREATEST(etapa_atual, 5) WHERE id = ?")
        ->execute([$parceiro_id]);

    $pdo->commit();

    $from    = $_POST['from'] ?? '';
    $destino = ($from === 'confirmacao') ? 'confirmacao.php' : 'etapa5_plataforma.php';
    header("Location: " . $destino);
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Erro ao salvar Etapa 4 do Parceiro: " . $e->getMessage());
    $_SESSION['erro_etapa4'] = "Erro ao salvar os interesses. Tente novamente.";
    header("Location: etapa4_interesses.php");
    exit;
}