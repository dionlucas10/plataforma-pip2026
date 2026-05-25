<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = require __DIR__ . '/app/config/db.php';

$pdo = new PDO(
    "mysql:host={$config['host']};dbname={$config['dbname']};port={$config['port']};charset={$config['charset']}",
    $config['user'],
    $config['pass'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

$pageTitle = 'Política de Posicionamento, Atuação e Isenção de Responsabilidade | Impactos Positivos';
include __DIR__ . '/app/views/public/header_public.php';
?>

<!-- HERO -->
<section class="reg-hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="reg-update-badge mb-3 d-inline-flex">
          <i class="bi bi-patch-check-fill me-1"></i>
          Última atualização: janeiro de 2026
        </span>
        <h1 class="reg-hero-title mb-3">
          Política de Posicionamento,<br>
          <span style="color:#CDDE00;">Atuação e Isenção de Responsabilidade</span>
        </h1>
        <p class="reg-hero-sub mb-4">
          Entenda o papel da Plataforma Impactos Positivos como dinamizadora do ecossistema de impacto, os limites de sua atuação e os termos de isenção de responsabilidade perante participantes, parceiros e terceiros.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="#sec-objetivo" class="btn-premiacao-outline">
            <i class="bi bi-file-text me-2"></i> Ler política completa
          </a>
          <a href="mailto:contato@impactospositivos.com" class="btn-premiacao-outline">
            <i class="bi bi-envelope me-2"></i> Falar conosco
          </a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="d-flex flex-column gap-2">
          <div class="reg-hero-info-item">
            <i class="bi bi-globe2" style="color:#CDDE00;"></i>
            Plataforma <strong>sem fins lucrativos 501(c)(3)</strong>
          </div>
          <div class="reg-hero-info-item">
            <i class="bi bi-diagram-3" style="color:#CDDE00;"></i>
            Atuação como <strong>vitrine e conexão</strong>
          </div>
          <div class="reg-hero-info-item">
            <i class="bi bi-shield-check" style="color:#CDDE00;"></i>
            Conforme as <strong>leis brasileiras vigentes</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /HERO -->


<!-- CONTEÚDO PRINCIPAL -->
<section class="py-5">
  <div class="container">
    <div class="row g-5">

      <!-- Índice lateral -->
      <aside class="col-lg-3 d-none d-lg-block">
        <nav class="reg-toc" aria-label="Índice da política de posicionamento">
          <div class="reg-toc-title"><i class="bi bi-list-ul me-1"></i> Índice</div>
          <ul class="reg-toc-list">
            <li><a href="#sec-objetivo"><span class="toc-num">1</span> Objetivo</a></li>
            <li><a href="#sec-natureza"><span class="toc-num">2</span> Natureza da Plataforma</a></li>
            <li><a href="#sec-atuacao"><span class="toc-num">3</span> Vitrine e Dinamização</a></li>
            <li><a href="#sec-vinculo"><span class="toc-num">4</span> Ausência de Vínculo</a></li>
            <li><a href="#sec-inclusividade"><span class="toc-num">5</span> Inclusividade</a></li>
            <li><a href="#sec-curadoria"><span class="toc-num">6</span> Curadoria e Due Diligence</a></li>
            <li><a href="#sec-isencao"><span class="toc-num">7</span> Isenção de Responsabilidade</a></li>
            <li><a href="#sec-participantes"><span class="toc-num">8</span> Responsabilidade dos Participantes</a></li>
            <li><a href="#sec-posicionamento"><span class="toc-num">9</span> Posicionamento Institucional</a></li>
            <li><a href="#sec-disposicoes"><span class="toc-num">10</span> Disposições Finais</a></li>
          </ul>
          <div class="reg-toc-cta">
            <a href="mailto:contato@impactospositivos.com" class="btn btn-success w-100" style="border-radius:999px;font-weight:700;">
              <i class="bi bi-envelope me-1"></i> Falar conosco
            </a>
          </div>
        </nav>
      </aside>

      <!-- Conteúdo -->
      <div class="col-lg-9">

        <!-- 1. Objetivo -->
        <section id="sec-objetivo" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">1</span>
            <h2 class="reg-section-title mb-0">Objetivo desta Política</h2>
          </div>
          <p>
            Esta Política tem como objetivo estabelecer, de forma clara, absoluta e transparente, o papel da <strong>Plataforma Impactos Positivos</strong> como dinamizadora de um ecossistema inclusivo e abundante de impacto positivo, bem como delimitar expressamente os limites de sua atuação, responsabilidades e vínculos com organizações, iniciativas, apoiadores, parceiros e demais participantes do ecossistema.
          </p>
        </section>

        <!-- 2. Natureza -->
        <section id="sec-natureza" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">2</span>
            <h2 class="reg-section-title mb-0">Natureza da Plataforma Impactos Positivos</h2>
          </div>
          <p>
            A Plataforma Impactos Positivos atua como uma <strong>plataforma digital de vitrine, exposição, conexão e articulação do ecossistema de impacto</strong>, nos moldes das grandes plataformas digitais globais, cuja atuação se caracteriza por:
          </p>
          <div class="row g-3 mb-3">
            <?php
            $natureza = [
              ['bi-broadcast',       'Ambientação Digital',   'Criar ambientes digitais e institucionais que conectam diferentes agentes de um ecossistema.'],
              ['bi-eye',             'Visibilidade',           'Oferecer visibilidade, curadoria e organização de informações e iniciativas.'],
              ['bi-people',          'Facilitação',           'Facilitar interações, relacionamentos, aprendizado e oportunidades.'],
              ['bi-lightning-charge','Catalisação',           'Atuar como catalisadora de conexões e possibilidades, sem interferir na execução das atividades dos participantes.'],
            ];
            foreach ($natureza as [$icon, $titulo, $desc]): ?>
              <div class="col-md-6">
                <div class="como-card h-100 text-start">
                  <i class="bi <?= $icon ?> como-icon"></i>
                  <h5><?= $titulo ?></h5>
                  <p><?= $desc ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="reg-alert-atencao d-flex gap-3 align-items-start">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
            <div>
              A Plataforma <strong>não executa, não gerencia, não controla e não representa</strong> as atividades realizadas por terceiros que participam do ecossistema.
            </div>
          </div>
        </section>

        <!-- 3. Atuação -->
        <section id="sec-atuacao" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">3</span>
            <h2 class="reg-section-title mb-0">Atuação como Vitrine, Conexão e Dinamização do Ecossistema</h2>
          </div>
          <p>A Plataforma Impactos Positivos promove e dinamiza o ecossistema por meio de diversas iniciativas, incluindo, mas não se limitando a:</p>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Prêmio Impactos Positivos;</li>
            <li>Programas de relacionamento, curadoria e visibilidade;</li>
            <li>Programas de aceleração, capacitação e desenvolvimento;</li>
            <li>Eventos, encontros, conteúdos e experiências;</li>
            <li>Conexões estratégicas entre organizações, pessoas, investidores, apoiadores e parceiros;</li>
            <li>Facilitação de contatos que podem ou não resultar em oportunidades comerciais, institucionais ou de investimento.</li>
          </ul>
          <p class="mt-3">Todas essas iniciativas têm caráter <strong>conectivo, educativo, inspirador e catalisador</strong>, sem configurar qualquer tipo de intermediação comercial, societária ou contratual.</p>
        </section>

        <!-- 4. Ausência de Vínculo -->
        <section id="sec-vinculo" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">4</span>
            <h2 class="reg-section-title mb-0">Ausência de Vínculo Legal, Comercial ou de Representação</h2>
          </div>
          <p>Fica expressamente estabelecido que:</p>

          <div class="reg-article">
            <span class="reg-article-num">4.1</span>
            A Plataforma Impactos Positivos é estruturada nos <strong>Estados Unidos</strong> como uma organização sem fins lucrativos <strong>501(c)(3)</strong>, sob a denominação <em>Impactos Positivos Global Platform Inc.</em>
          </div>

          <div class="reg-article">
            <span class="reg-article-num">4.2</span>
            No Brasil, a Plataforma é representada institucionalmente pela <strong>Global Vision Access Comunicação e Marketing LTDA</strong>, CNPJ nº 08.817.535/0001-61, exclusivamente para fins operacionais, administrativos e institucionais.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">4.3</span>
            Essa estruturação e representação <strong>não criam vínculo societário, trabalhista, contratual, comercial, jurídico ou de representação comercial</strong> entre a Plataforma e as organizações, empresas, parceiros, apoiadores ou participantes do ecossistema.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">4.4</span>
            A participação no ecossistema não cria qualquer relação de agência, mandato, franquia, <em>joint venture</em>, sociedade ou representação comercial.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">4.5</span>
            Nenhum participante está autorizado a se apresentar como representante, porta-voz ou agente da Plataforma Impactos Positivos.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">4.6</span>
            A Plataforma <strong>não endossa, não garante e não se responsabiliza</strong> por produtos, serviços, decisões, práticas, promessas ou resultados de terceiros.
          </div>
        </section>

        <!-- 5. Inclusividade -->
        <section id="sec-inclusividade" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">5</span>
            <h2 class="reg-section-title mb-0">Inclusividade e Abundância do Ecossistema</h2>
          </div>
          <p>
            O ecossistema Impactos Positivos é <strong>aberto, inclusivo e baseado no princípio da abundância</strong>, no qual múltiplas soluções, modelos de negócio, organizações e iniciativas podem coexistir, colaborar e evoluir.
          </p>
          <p>
            A presença de uma organização no ecossistema <strong>não implica exclusividade, preferência, chancela formal ou validação absoluta</strong> por parte da Plataforma Impactos Positivos.
          </p>
        </section>

        <!-- 6. Curadoria -->
        <section id="sec-curadoria" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">6</span>
            <h2 class="reg-section-title mb-0">Curadoria e Due Diligence</h2>
          </div>
          <p>
            A Plataforma Impactos Positivos realiza processos de curadoria e análise preliminar (<em>due diligence</em>) aplicáveis a parceiros, apoiadores, patrocinadores, embaixadores, voluntários, bem como às empresas (negócios de impacto) e seus fundadores que se inscrevem e participam das iniciativas da Plataforma, incluindo a <strong>Vitrine de Impacto</strong> e o <strong>Prêmio Impactos Positivos</strong>.
          </p>
          <p>Esses processos são conduzidos com base exclusivamente em <strong>informações públicas</strong>, materiais disponibilizados voluntariamente pelos próprios inscritos e fontes abertas consideradas confiáveis.</p>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-check2-square me-2"></i>Objetivos da curadoria</h5>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Avaliar o alinhamento institucional, reputacional, ético e de propósito com os valores e princípios do ecossistema Impactos Positivos;</li>
            <li>Verificar a coerência entre discurso público, práticas conhecidas e posicionamento declarado;</li>
            <li>Apoiar decisões de inclusão, permanência, destaque ou reconhecimento dentro das iniciativas da Plataforma.</li>
          </ul>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-x-circle me-2"></i>O que a curadoria não é</h5>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Não constitui auditoria, certificação, homologação, endosso, garantia de conformidade legal ou recomendação de investimento;</li>
            <li>Não substitui análises jurídicas, financeiras, operacionais, reputacionais ou regulatórias aprofundadas, que permanecem sob responsabilidade exclusiva de terceiros interessados;</li>
            <li>Baseia-se em um recorte temporal e informacional limitado, sujeito a alterações futuras.</li>
          </ul>

          <div class="reg-article mt-3">
            Todos os parceiros, apoiadores, patrocinadores, embaixadores, voluntários, empresas e fundadores comprometem-se a <strong>informar tempestivamente</strong> à Plataforma Impactos Positivos qualquer mudança relevante em suas estruturas, operações, práticas, lideranças ou posicionamentos que possam impactar sua relação com o ecossistema.
          </div>
          <div class="reg-article">
            A Plataforma reserva-se o direito de <strong>reavaliar, suspender ou encerrar vínculos institucionais, participações, inscrições ou destaques</strong>, caso identifique desalinhamentos relevantes com seus princípios, valores ou diretrizes de conduta.
          </div>
        </section>

        <!-- 7. Isenção -->
        <section id="sec-isencao" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">7</span>
            <h2 class="reg-section-title mb-0">Isenção Integral de Responsabilidade</h2>
          </div>
          <p>A Plataforma Impactos Positivos <strong>não se responsabiliza, em nenhuma hipótese</strong>, por:</p>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Atos, omissões, decisões, condutas ou resultados de organizações, empresas ou indivíduos participantes do ecossistema;</li>
            <li>Relações comerciais, contratuais, societárias, financeiras ou de investimento estabelecidas entre terceiros;</li>
            <li>Perdas, danos, prejuízos, riscos, expectativas frustradas ou impactos decorrentes de interações realizadas a partir de conexões promovidas pela Plataforma;</li>
            <li>Conteúdos, informações, promessas ou práticas divulgadas por terceiros.</li>
          </ul>

          <div class="reg-alert-atencao d-flex gap-3 align-items-start mt-3">
            <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
            <div>
              Nenhum colaborador, representante, consultor ou membro da equipe da Plataforma Impactos Positivos está autorizado a oferecer garantias sobre a atuação, desempenho ou resultados de organizações participantes, nem emitir referências formais, recomendações comerciais ou validações institucionais em nome da Plataforma. Quaisquer manifestações feitas por colaboradores devem ser compreendidas como <strong>opiniões pessoais</strong>, que não vinculam nem representam a Plataforma.
            </div>
          </div>

          <div class="reg-article mt-3">
            Toda e qualquer decisão tomada por participantes do ecossistema é de <strong>responsabilidade exclusiva das partes envolvidas</strong>.
          </div>
        </section>

        <!-- 8. Responsabilidade dos Participantes -->
        <section id="sec-participantes" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">8</span>
            <h2 class="reg-section-title mb-0">Responsabilidade dos Participantes</h2>
          </div>
          <p>Os participantes do ecossistema Impactos Positivos declaram estar cientes de que:</p>
          <div class="row g-3">
            <?php
            $responsabilidades = [
              ['bi-person-check',    'Veracidade',       'São integralmente responsáveis pela veracidade, legalidade e atualidade das informações que divulgam.'],
              ['bi-journal-check',  'Conformidade',     'Devem cumprir a legislação vigente, princípios éticos e boas práticas de mercado.'],
              ['bi-search',         'Due Diligence',    'Devem realizar suas próprias análises, validações e avaliações antes de firmar qualquer tipo de relação com terceiros.'],
              ['bi-bell',           'Notificação',      'Devem informar tempestivamente à Plataforma qualquer mudança relevante em suas estruturas, práticas ou lideranças.'],
            ];
            foreach ($responsabilidades as [$icon, $titulo, $desc]): ?>
              <div class="col-md-6">
                <div class="como-card h-100 text-start">
                  <i class="bi <?= $icon ?> como-icon"></i>
                  <h5><?= $titulo ?></h5>
                  <p><?= $desc ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- 9. Posicionamento Institucional -->
        <section id="sec-posicionamento" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">9</span>
            <h2 class="reg-section-title mb-0">Posicionamento Institucional</h2>
          </div>
          <p>
            A Plataforma Impactos Positivos atua com uma abordagem <strong>construtiva, positiva, ética, transparente e colaborativa</strong>, com o propósito de fortalecer o ecossistema de impacto e ampliar as possibilidades de transformação positiva.
          </p>
          <p>
            Este posicionamento <strong>não implica corresponsabilidade, garantia de resultado ou envolvimento operacional</strong> nas ações de terceiros.
          </p>
        </section>

        <!-- 10. Disposições Finais -->
        <section id="sec-disposicoes" class="reg-section" style="border-bottom:0;">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">10</span>
            <h2 class="reg-section-title mb-0">Disposições Finais</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">10.1</span>
            Esta Política poderá ser atualizada periodicamente para refletir a evolução da Plataforma e do ecossistema.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">10.2</span>
            Ao participar de qualquer iniciativa da Plataforma Impactos Positivos, o participante declara que <strong>leu, compreendeu e concorda integralmente</strong> com os termos aqui estabelecidos.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">10.3</span>
            Esta Política está em conformidade e deverá ser interpretada com base nas <strong>leis vigentes na República Federativa do Brasil</strong>.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">10.4</span>
            Para dirimir eventuais dúvidas ou questões relativas a ela, as partes elegem o <strong>Foro da Comarca de São Paulo/SP</strong>, com exclusão de qualquer outro.
          </div>

          <div class="reg-promotora-box mt-4">
            <div class="row g-3">
              <div class="col-md-6">
                <p class="mb-1"><strong>Plataforma:</strong> Impactos Positivos Global Platform Inc.</p>
                <p class="mb-1"><strong>Representante no Brasil:</strong> Global Vision Access Comunicação e Marketing LTDA</p>
                <p class="mb-1"><strong>CNPJ:</strong> 08.817.535/0001-61</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>E-mail:</strong> <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a></p>
                <p class="mb-1"><strong>Endereço:</strong> Rua Apeninos, 429, cj. 1206 — Aclimação, São Paulo / SP</p>
                <p class="mb-0"><strong>Última atualização:</strong> janeiro de 2026.</p>
              </div>
            </div>
          </div>
        </section>

        <!-- CTA final -->
        <div class="cta-box mt-5">
          <div class="row align-items-center g-3">
            <div class="col-md-8">
              <h3 class="cta-title mb-1"><i class="bi bi-question-circle me-2"></i>Dúvidas sobre esta política?</h3>
              <p class="cta-sub mb-0">Entre em contato com nossa equipe. Respondemos em até 10 dias úteis.</p>
            </div>
            <div class="col-md-4 text-md-end">
              <a href="mailto:contato@impactospositivos.com" class="btn-cta-parceiro">
                <i class="bi bi-envelope me-2"></i> Falar conosco
              </a>
            </div>
          </div>
        </div>

      </div>
      <!-- /col conteúdo -->
    </div>
    <!-- /row -->
  </div>
  <!-- /container -->
</section>
<!-- /CONTEÚDO -->


<script>
  (function () {
    const links = document.querySelectorAll('.reg-toc-list a');
    const targets = [];
    links.forEach(function (link) {
      const href = link.getAttribute('href');
      if (href && href.startsWith('#')) {
        const el = document.querySelector(href);
        if (el) targets.push({ link, el });
      }
    });
    function onScroll() {
      let current = null;
      targets.forEach(function ({ el }) {
        if (window.scrollY >= el.offsetTop - 120) current = el;
      });
      links.forEach(function (l) { l.classList.remove('reg-toc-link--active'); });
      if (current) {
        const match = targets.find(function (t) { return t.el === current; });
        if (match) match.link.classList.add('reg-toc-link--active');
      }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
  })();
</script>

<?php include __DIR__ . '/app/views/public/footer_public.php'; ?>