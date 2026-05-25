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

$pageTitle = 'Central de Ajuda | Impactos Positivos';
include __DIR__ . '/app/views/public/header_public.php';
?>

<!-- HERO -->
<section class="reg-hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="reg-update-badge mb-3 d-inline-flex">
          <i class="bi bi-patch-check-fill me-1"></i>
          Última atualização: maio de 2026
        </span>
        <h1 class="reg-hero-title mb-3">
          Central de Ajuda<br>
          <span style="color:#CDDE00;">Plataforma Impactos Positivos</span>
        </h1>
        <p class="reg-hero-sub mb-4">
          Este espaço foi criado para oferecer clareza, transparência e orientação sobre como a Plataforma funciona, qual é o nosso papel no ecossistema e quais são as responsabilidades de todos os participantes.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="#sec-o-que-e" class="btn-premiacao-outline">
            <i class="bi bi-file-text me-2"></i> Ler conteúdo completo
          </a>
          <a href="mailto:contato@impactospositivos.com" class="btn-premiacao-outline">
            <i class="bi bi-envelope me-2"></i> Falar conosco
          </a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="d-flex flex-column gap-2">
          <div class="reg-hero-info-item">
            <i class="bi bi-lightbulb" style="color:#CDDE00;"></i>
            Plataforma de <strong>vitrine e conexão</strong>
          </div>
          <div class="reg-hero-info-item">
            <i class="bi bi-people" style="color:#CDDE00;"></i>
            Ecossistema <strong>aberto e inclusivo</strong>
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
        <nav class="reg-toc" aria-label="Índice da Central de Ajuda">
          <div class="reg-toc-title"><i class="bi bi-list-ul me-1"></i> Índice</div>
          <ul class="reg-toc-list">
            <li><a href="#sec-definicoes"><span class="toc-num">—</span> Definições</a></li>
            <li><a href="#sec-porque"><span class="toc-num">—</span> Por que fazer parte?</a></li>
            <li><a href="#sec-o-que-e"><span class="toc-num">1</span> O que é a Plataforma?</a></li>
            <li><a href="#sec-papel"><span class="toc-num">2</span> Papel no ecossistema</a></li>
            <li><a href="#sec-nao-faz"><span class="toc-num">3</span> O que a Plataforma NÃO faz</a></li>
            <li><a href="#sec-quem-pode"><span class="toc-num">4</span> Quem pode participar?</a></li>
            <li><a href="#sec-vitrine-premio"><span class="toc-num">5</span> Vitrine e Prêmio</a></li>
            <li><a href="#sec-curadoria"><span class="toc-num">6</span> Curadoria e Due Diligence</a></li>
            <li><a href="#sec-responsabilidades"><span class="toc-num">7</span> Responsabilidades</a></li>
            <li><a href="#sec-parceiros"><span class="toc-num">8</span> Parceiros e Embaixadores</a></li>
            <li><a href="#sec-garantias"><span class="toc-num">9</span> Garantias</a></li>
            <li><a href="#sec-estrutura"><span class="toc-num">10</span> Estrutura institucional</a></li>
            <li><a href="#sec-ecossistema"><span class="toc-num">11</span> O ecossistema</a></li>
            <li><a href="#sec-duvidas"><span class="toc-num">12</span> Dúvidas</a></li>
            <li><a href="#sec-feedback"><span class="toc-num">13</span> Feedback</a></li>
            <li><a href="#sec-atualizacoes"><span class="toc-num">14</span> Atualizações e aceite</a></li>
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

        <!-- Boas-vindas -->
        <div class="reg-alert-atencao d-flex gap-3 align-items-start mb-5">
          <i class="bi bi-hand-wave fs-5 flex-shrink-0 mt-1"></i>
          <div>
            <strong>Bem-vindo(a) à Central de Ajuda da Plataforma Impactos Positivos!</strong><br>
            Nosso compromisso é apoiar um ecossistema ético, colaborativo, inclusivo e em constante evolução.
          </div>
        </div>

        <!-- Definições -->
        <section id="sec-definicoes" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <h2 class="reg-section-title mb-0">Definições</h2>
          </div>
          <p>Para facilitar a compreensão dos conteúdos e funcionamento da Plataforma, apresentamos os principais termos utilizados ao longo do ecossistema:</p>

          <?php
          $definicoes = [
            ['Ecossistema de impacto',     'Conjunto de pessoas, empresas, organizações, investidores, projetos e iniciativas que atuam de forma conectada para gerar impacto social, ambiental e econômico positivo na sociedade.'],
            ['Negócios de impacto',         'Empresas ou iniciativas que buscam gerar lucro de forma sustentável enquanto solucionam desafios sociais, ambientais ou econômicos por meio de seus produtos, serviços ou atuação.'],
            ['Empreendedorismo de impacto', 'Modelo de empreendedorismo voltado à criação de soluções inovadoras que promovam transformação positiva na sociedade, conciliando propósito, sustentabilidade financeira e impacto.'],
            ['ESG',                         'Sigla para Environmental, Social and Governance (Ambiental, Social e Governança). Refere-se a práticas adotadas por organizações relacionadas à responsabilidade ambiental, impacto social e gestão ética e transparente.'],
            ['Inovação social',             'Desenvolvimento de novas soluções, produtos, serviços, metodologias ou modelos capazes de responder a desafios sociais de forma mais eficiente, inclusiva e sustentável.'],
            ['Stakeholders',               'Pessoas, grupos ou organizações que podem impactar ou ser impactados pelas atividades de uma empresa, projeto ou iniciativa, como clientes, parceiros, comunidades, colaboradores, investidores e fornecedores.'],
            ['Due diligence',              'Processo de análise preliminar realizado com base em informações públicas e materiais fornecidos, com o objetivo de avaliar alinhamento institucional, reputação, coerência e conformidade de participantes ou organizações.'],
            ['Curadoria',                  'Processo de seleção, avaliação e organização de participantes, conteúdos, projetos ou iniciativas, considerando critérios de qualidade, alinhamento de propósito, relevância e aderência aos valores da Plataforma.'],
            ['Vitrine de Impacto',         'Espaço da Plataforma destinado à apresentação e divulgação de empresas, organizações e iniciativas que atuam com impacto positivo, permitindo maior visibilidade e conexão com o ecossistema.'],
            ['Embaixadores e apoiadores',  'Pessoas ou organizações que contribuem para fortalecer, divulgar, apoiar ou representar institucionalmente a Plataforma e suas iniciativas, ajudando na expansão do ecossistema e no engajamento da comunidade.'],
          ];
          foreach ($definicoes as [$termo, $desc]): ?>
            <div class="reg-article">
              <strong><?= $termo ?>:</strong> <?= $desc ?>
            </div>
          <?php endforeach; ?>
        </section>

        <!-- Por que fazer parte -->
        <section id="sec-porque" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <h2 class="reg-section-title mb-0">Por que fazer parte do ecossistema?</h2>
          </div>
          <p>A Plataforma Impactos Positivos busca conectar pessoas, organizações e iniciativas comprometidas com a geração de impacto positivo, promovendo colaboração, visibilidade e fortalecimento de ações transformadoras.</p>
          <p>Entre os benefícios e oportunidades proporcionados pelo ecossistema, destacam-se:</p>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Conexão com empresas, líderes, empreendedores e organizações alinhadas a impacto positivo;</li>
            <li>Oportunidades de networking, relacionamento e visibilidade institucional;</li>
            <li>Participação em eventos, premiações, ações especiais e iniciativas promovidas pela Plataforma;</li>
            <li>Fortalecimento institucional e posicionamento de marca no ecossistema de impacto;</li>
            <li>Acesso a conteúdo, conexões e oportunidades estratégicas;</li>
            <li>Divulgação de iniciativas, projetos e organizações por meio da Vitrine de Impacto e canais institucionais;</li>
            <li>Integração a uma comunidade colaborativa, diversa e transformadora;</li>
            <li>Possibilidade de ampliar conexões nacionais e internacionais relacionadas ao impacto positivo.</li>
          </ul>
          <div class="reg-alert-atencao d-flex gap-3 align-items-start">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
            <div>
              A participação no ecossistema <strong>não garante benefícios financeiros, certificações ou resultados específicos</strong>, mas proporciona oportunidades de conexão, visibilidade, aprendizado e colaboração entre os participantes da comunidade.
            </div>
          </div>
        </section>

        <!-- 1. O que é -->
        <section id="sec-o-que-e" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">1</span>
            <h2 class="reg-section-title mb-0">O que é a Plataforma Impactos Positivos?</h2>
          </div>
          <p>A Plataforma Impactos Positivos é uma plataforma digital de vitrine, conexão e dinamização do ecossistema de impacto. Atuamos como um ambiente que amplia a visibilidade, promove conexões estratégicas e estimula colaboração entre organizações, pessoas e iniciativas comprometidas com impacto positivo.</p>
          <div class="row g-3 mb-3">
            <?php
            $oQue = [
              ['bi-people',           'Conecta',     'Agentes do ecossistema de impacto.'],
              ['bi-eye',              'Dá visibilidade', 'A iniciativas, negócios, organizações e lideranças.'],
              ['bi-trophy',           'Promove',     'Programas, prêmios, conteúdos, eventos e experiências.'],
              ['bi-arrow-left-right', 'Facilita',    'Relacionamentos que podem ou não gerar oportunidades futuras.'],
            ];
            foreach ($oQue as [$icon, $titulo, $desc]): ?>
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
              A Plataforma <strong>não executa, não gerencia, não controla e não representa</strong> as atividades realizadas por terceiros.
            </div>
          </div>
        </section>

        <!-- 2. Papel -->
        <section id="sec-papel" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">2</span>
            <h2 class="reg-section-title mb-0">Qual é o papel da Plataforma no ecossistema?</h2>
          </div>
          <p>Nosso papel é catalisar possibilidades, organizar informações e criar ambientes favoráveis à colaboração e ao fortalecimento do ecossistema de impacto.</p>
          <p>Não atuamos como intermediários comerciais, representantes legais, agentes de investimento ou gestores de negócios. Todas as decisões, relações e acordos são de responsabilidade exclusiva das partes envolvidas.</p>
        </section>

        <!-- 3. O que NÃO faz -->
        <section id="sec-nao-faz" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">3</span>
            <h2 class="reg-section-title mb-0">O que a Plataforma NÃO faz?</h2>
          </div>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Não representa empresas, organizações, investidores ou pessoas;</li>
            <li>Não intermedeia contratos, investimentos ou negociações;</li>
            <li>Não garante resultados, impactos, retornos financeiros ou reputacionais;</li>
            <li>Não valida, certifica ou audita organizações;</li>
            <li>Não se responsabiliza por decisões ou ações de terceiros.</li>
          </ul>
        </section>

        <!-- 4. Quem pode participar -->
        <section id="sec-quem-pode" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">4</span>
            <h2 class="reg-section-title mb-0">Quem pode participar da Plataforma?</h2>
          </div>
          <p>Podem participar do ecossistema Impactos Positivos:</p>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Empresas e negócios de impacto;</li>
            <li>Organizações da sociedade civil;</li>
            <li>Fundadores, líderes e empreendedores;</li>
            <li>Parceiros, apoiadores e patrocinadores;</li>
            <li>Embaixadores e voluntários;</li>
            <li>Pessoas interessadas em impacto positivo.</li>
          </ul>
          <p>A participação é aberta, inclusiva e não exclusiva.</p>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-person-plus me-2"></i>Como participar?</h5>
          <p>Para participar, o interessado deverá realizar seu cadastro em <a href="https://www.impactospositivos.com" class="reg-ext-link" target="_blank">www.impactospositivos.com</a>, preenchendo as informações solicitadas e, quando aplicável, enviando materiais complementares. Dependendo da modalidade de participação, poderão ser solicitados:</p>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Dados cadastrais e institucionais;</li>
            <li>Informações sobre projetos, atividades ou iniciativas;</li>
            <li>Materiais de apresentação;</li>
            <li>Redes sociais, site ou canais oficiais;</li>
            <li>Documentos complementares, quando necessário.</li>
          </ul>
          <div class="reg-alert-atencao d-flex gap-3 align-items-start">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
            <div>
              O envio do cadastro <strong>não garante aprovação automática</strong>, participação em ações específicas ou utilização da marca da Plataforma.
            </div>
          </div>

          <h5 class="reg-fase-subtitulo mt-4"><i class="bi bi-check2-circle me-2"></i>Processo de aprovação</h5>
          <p>Após o envio das informações, a Plataforma poderá realizar uma análise de curadoria e alinhamento institucional, considerando critérios como:</p>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Compatibilidade com os valores e propósito da iniciativa;</li>
            <li>Atuação ética e reputacional;</li>
            <li>Coerência entre discurso, práticas e impacto gerado;</li>
            <li>Potencial de contribuição para o ecossistema;</li>
            <li>Adequação às diretrizes, projetos e objetivos da Plataforma.</li>
          </ul>
          <p>A aprovação, não aprovação ou continuidade no ecossistema ocorrerá a critério da Plataforma, que poderá aceitar, recusar, suspender ou encerrar participações quando entender necessário para preservar seus princípios e propósito.</p>
        </section>

        <!-- 5. Vitrine e Prêmio -->
        <section id="sec-vitrine-premio" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">5</span>
            <h2 class="reg-section-title mb-0">O que são a Vitrine de Impacto e o Prêmio Impactos Positivos?</h2>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="como-card h-100 text-start">
                <i class="bi bi-grid-3x3-gap como-icon"></i>
                <h5>Vitrine de Impacto</h5>
                <p>Espaço de exposição e visibilidade para empresas, organizações e iniciativas que se inscrevem voluntariamente na Plataforma.</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="como-card h-100 text-start">
                <i class="bi bi-trophy como-icon"></i>
                <h5>Prêmio Impactos Positivos</h5>
                <p>Iniciativa de reconhecimento e celebração de iniciativas alinhadas aos valores do ecossistema.</p>
              </div>
            </div>
          </div>
          <div class="reg-alert-atencao d-flex gap-3 align-items-start mt-3">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
            <div>
              A inscrição na Vitrine de Impacto ou no Prêmio Impactos Positivos <strong>não cria vínculo legal, comercial ou de representação</strong> com a Plataforma.
            </div>
          </div>
        </section>

        <!-- 6. Curadoria -->
        <section id="sec-curadoria" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">6</span>
            <h2 class="reg-section-title mb-0">Como funciona a curadoria e a due diligence?</h2>
          </div>
          <p>A Plataforma realiza processos de curadoria e análise preliminar (<em>due diligence</em>) aplicáveis a empresas e seus fundadores, parceiros, apoiadores, patrocinadores, embaixadores, voluntários e inscritos na Vitrine de Impacto e no Prêmio Impactos Positivos.</p>
          <p>Esses processos são baseados exclusivamente em informações públicas e materiais fornecidos voluntariamente, avaliando alinhamento de valores, propósito, reputação e coerência institucional. <strong>Não constituem auditoria, certificação, endosso ou recomendação de investimento.</strong></p>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-exclamation-circle me-2"></i>Critérios que podem impactar a aprovação ou permanência</h5>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Informações inconsistentes, falsas ou incompletas;</li>
            <li>Condutas antiéticas, discriminatórias ou incompatíveis com os valores da Plataforma;</li>
            <li>Atividades ilegais ou que possam gerar riscos reputacionais ao ecossistema;</li>
            <li>Falta de alinhamento com os objetivos e propósito da iniciativa;</li>
            <li>Uso inadequado da marca, imagem ou canais da Plataforma;</li>
            <li>Descumprimento dos termos, regulamentos ou acordos firmados.</li>
          </ul>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-megaphone me-2"></i>Comunicação dos resultados</h5>
          <p>As comunicações relacionadas aos processos de curadoria, aprovação, reavaliação, suspensão ou encerramento poderão ser realizadas por meio de e-mail cadastrado, plataforma oficial, WhatsApp ou canais institucionais oficiais.</p>
          <div class="reg-article">
            A Plataforma reserva-se o direito de <strong>não divulgar publicamente os motivos específicos</strong> relacionados aos processos internos de avaliação e curadoria.
          </div>
        </section>

        <!-- 7. Responsabilidades dos participantes -->
        <section id="sec-responsabilidades" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">7</span>
            <h2 class="reg-section-title mb-0">Quais são as responsabilidades dos participantes?</h2>
          </div>
          <div class="row g-3">
            <?php
            $responsabilidades = [
              ['bi-search',        'Due Diligence',   'Verificar e avaliar as organizações e pessoas com quem se relacionam, realizando sua própria due diligence antes de firmar acordos ou parcerias.'],
              ['bi-check2-square', 'Veracidade',      'Garantir a veracidade e atualização das informações que divulgam.'],
              ['bi-handshake',     'Ética',           'Atuar de forma ética, legal, positiva e colaborativa.'],
              ['bi-bell',          'Transparência',   'Comunicar qualquer mudança relevante em suas atividades à Plataforma.'],
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

        <!-- 8. Parceiros e embaixadores -->
        <section id="sec-parceiros" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">8</span>
            <h2 class="reg-section-title mb-0">Responsabilidades de parceiros, apoiadores, embaixadores e voluntários</h2>
          </div>
          <p>Parceiros, apoiadores, patrocinadores, embaixadores e voluntários assinam uma Carta-Acordo com a Plataforma, comprometendo-se com conduta ética, positiva e colaborativa, devendo comunicar qualquer mudança relevante em suas atividades.</p>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-x-circle me-2"></i>Motivos para desclassificação ou remoção</h5>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Condutas consideradas antiéticas, ofensivas, discriminatórias ou que prejudiquem terceiros;</li>
            <li>Utilização da Plataforma para fins ilícitos, enganosos ou incompatíveis com os objetivos da iniciativa;</li>
            <li>Divulgação de informações falsas ou documentação irregular;</li>
            <li>Descumprimento dos termos acordados na Carta-Acordo ou das diretrizes da Plataforma;</li>
            <li>Ações que possam comprometer a reputação, integridade ou segurança da comunidade Impactos Positivos.</li>
          </ul>

          <div class="reg-article mt-3">
            <strong>Solicitação de descadastramento:</strong> O parceiro, apoiador, embaixador ou voluntário poderá solicitar seu descadastramento a qualquer momento, mediante solicitação formal enviada para <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a>.
          </div>
        </section>

        <!-- 9. Garantias -->
        <section id="sec-garantias" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">9</span>
            <h2 class="reg-section-title mb-0">A Plataforma garante ou recomenda empresas?</h2>
          </div>
          <div class="reg-alert-atencao d-flex gap-3 align-items-start">
            <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0 mt-1"></i>
            <div>
              <strong>Não.</strong> A Plataforma Impactos Positivos não garante, recomenda ou valida formalmente empresas, organizações ou pessoas. Nenhum colaborador, representante ou membro do time está autorizado a oferecer garantias ou referências em nome da Plataforma. Opiniões eventualmente expressas devem ser entendidas como <strong>opiniões pessoais</strong>.
            </div>
          </div>
        </section>

        <!-- 10. Estrutura -->
        <section id="sec-estrutura" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">10</span>
            <h2 class="reg-section-title mb-0">Estrutura institucional da Plataforma</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">10.1</span>
            A Plataforma Impactos Positivos é estruturada nos <strong>Estados Unidos</strong> como uma organização sem fins lucrativos <strong>501(c)(3)</strong>, denominada <em>Impactos Positivos Global Platform</em>.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">10.2</span>
            É representada no Brasil pela <strong>Global Vision Access Comunicação e Marketing LTDA</strong> (CNPJ 08.817.535/0001-61), exclusivamente para fins institucionais, administrativos e operacionais.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">10.3</span>
            Essa estrutura <strong>não cria vínculos comerciais ou de representação</strong> com participantes do ecossistema.
          </div>
        </section>

        <!-- 11. Ecossistema -->
        <section id="sec-ecossistema" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">11</span>
            <h2 class="reg-section-title mb-0">O ecossistema Impactos Positivos</h2>
          </div>
          <div class="row g-3">
            <?php
            $ecossistema = [
              ['bi-globe2',         'Aberto e inclusivo',           'Qualquer pessoa ou organização alinhada ao propósito pode fazer parte.'],
              ['bi-infinity',       'Princípio da abundância',       'Baseado na crença de que múltiplos agentes podem colaborar e crescer juntos.'],
              ['bi-people-fill',    'Construção colaborativa',       'O ecossistema é construído coletivamente por todos os participantes.'],
              ['bi-shield-check',   'Responsabilidade coletiva',     'A evolução do ecossistema depende do compromisso de todos.'],
            ];
            foreach ($ecossistema as [$icon, $titulo, $desc]): ?>
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

        <!-- 12. Dúvidas -->
        <section id="sec-duvidas" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">12</span>
            <h2 class="reg-section-title mb-0">Dúvidas</h2>
          </div>
          <p>A Plataforma Impactos Positivos e seu time estão sempre disponíveis para apoiar, orientar e facilitar diálogos em casos de dúvidas, conflitos ou insatisfações.</p>
          <p>Para contato direto, orientações ou solicitação de apoio, escreva para: <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a></p>
          <div class="reg-alert-atencao d-flex gap-3 align-items-start">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 mt-1"></i>
            <div>
              Esse apoio é de caráter <strong>facilitador e conciliador</strong>, não configurando arbitragem formal, julgamento ou assunção de responsabilidade jurídica.
            </div>
          </div>
        </section>

        <!-- 13. Feedback -->
        <section id="sec-feedback" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">13</span>
            <h2 class="reg-section-title mb-0">Feedback e Evolução do Ecossistema</h2>
          </div>
          <p>A Plataforma valoriza o feedback contínuo e construtivo como parte fundamental da evolução do ecossistema. Usuários e participantes podem compartilhar percepções, sugestões, aprendizados e feedbacks sobre:</p>
          <ul style="padding-left:1.5rem;line-height:2;">
            <li>Ações, programas e iniciativas da Plataforma;</li>
            <li>Empresas, organizações e iniciativas participantes do ecossistema;</li>
            <li>Experiências vivenciadas a partir de conexões realizadas.</li>
          </ul>
          <p>Os feedbacks e sugestões devem ser enviados para: <a href="mailto:feedbacks@impactospositivos.com" class="reg-ext-link">feedbacks@impactospositivos.com</a></p>
          <div class="reg-article">
            Os feedbacks devem ser enviados de forma <strong>ética, respeitosa, responsável e fundamentada</strong>, sempre com o objetivo de fortalecer o ecossistema. A Plataforma poderá considerar essas contribuições em seus processos internos sem que isso gere qualquer obrigação de intervenção ou posicionamento público.
          </div>
        </section>

        <!-- 14. Atualizações -->
        <section id="sec-atualizacoes" class="reg-section" style="border-bottom:0;">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">14</span>
            <h2 class="reg-section-title mb-0">Atualizações e aceite</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">14.1</span>
            Esta Central de Ajuda pode ser <strong>atualizada periodicamente</strong>.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">14.2</span>
            Ao utilizar a Plataforma Impactos Positivos ou participar de qualquer iniciativa, você declara que <strong>leu, compreendeu e concorda</strong> com estas orientações e com a Política de Posicionamento, Atuação e Isenção de Responsabilidade da Plataforma.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">14.3</span>
            Este documento está em conformidade e deverá ser interpretado com base nas <strong>leis vigentes na República Federativa do Brasil</strong>. Para dirimir eventuais dúvidas, as partes elegem o <strong>Foro da Comarca de São Paulo/SP</strong>.
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
                <p class="mb-1"><strong>Feedbacks:</strong> <a href="mailto:feedbacks@impactospositivos.com" class="reg-ext-link">feedbacks@impactospositivos.com</a></p>
                <p class="mb-0"><strong>Última atualização:</strong> maio de 2026.</p>
              </div>
            </div>
          </div>
        </section>

        <!-- CTA final -->
        <div class="cta-box mt-5">
          <div class="row align-items-center g-3">
            <div class="col-md-8">
              <h3 class="cta-title mb-1"><i class="bi bi-question-circle me-2"></i>Ainda tem dúvidas?</h3>
              <p class="cta-sub mb-0">Entre em contato com nossa equipe. Estamos aqui para ajudar.</p>
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