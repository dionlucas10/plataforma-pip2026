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

$pageTitle = 'Política de Privacidade | Impactos Positivos';
include __DIR__ . '/app/views/public/header_public.php';
?>

<!-- ═══════════════════════════════════════
     HERO — POLÍTICA DE PRIVACIDADE
════════════════════════════════════════ -->
<section class="reg-hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="reg-update-badge mb-3 d-inline-flex">
          <i class="bi bi-patch-check-fill me-1"></i>
          Última atualização: maio de 2026
        </span>
        <h1 class="reg-hero-title mb-3">
          Política de<br>
          <span style="color:#CDDE00;">Privacidade</span>
        </h1>
        <p class="reg-hero-sub mb-4">
          Saiba como coletamos, usamos e protegemos seus dados pessoais na plataforma Impactos Positivos, em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018).
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="#sec-intro" class="btn-premiacao-outline">
            <i class="bi bi-shield-check me-2"></i> Ler política completa
          </a>
          <a href="mailto:contato@impactospositivos.com" class="btn-premiacao-outline">
            <i class="bi bi-envelope me-2"></i> Falar com o DPO
          </a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="d-flex flex-column gap-2">
          <div class="reg-hero-info-item">
            <i class="bi bi-shield-lock" style="color:#CDDE00;"></i>
            Conformidade com a <strong>LGPD</strong>
          </div>
          <div class="reg-hero-info-item">
            <i class="bi bi-person-lock" style="color:#CDDE00;"></i>
            Seus direitos como <strong>titular de dados</strong>
          </div>
          <div class="reg-hero-info-item">
            <i class="bi bi-envelope-at" style="color:#CDDE00;"></i>
            Contato: <strong>contato@impactospositivos.com</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- /HERO -->


<!-- ═══════════════════════════════════════
     CONTEÚDO PRINCIPAL
════════════════════════════════════════ -->
<section class="py-5">
  <div class="container">
    <div class="row g-5">

      <!-- ── Índice lateral -->
      <aside class="col-lg-3 d-none d-lg-block">
        <nav class="reg-toc" aria-label="Índice da política de privacidade">
          <div class="reg-toc-title"><i class="bi bi-list-ul me-1"></i> Índice</div>
          <ul class="reg-toc-list">
            <li><a href="#sec-intro"><span class="toc-num">–</span> Introdução</a></li>
            <li><a href="#sec-quem-somos"><span class="toc-num">1</span> Quem Somos</a></li>
            <li><a href="#sec-dados-coletados"><span class="toc-num">2</span> Dados Coletados</a></li>
            <li><a href="#sec-finalidades"><span class="toc-num">3</span> Finalidades</a></li>
            <li><a href="#sec-bases-legais"><span class="toc-num">4</span> Bases Legais</a></li>
            <li><a href="#sec-compartilhamento"><span class="toc-num">5</span> Compartilhamento</a></li>
            <li><a href="#sec-cookies"><span class="toc-num">6</span> Cookies</a></li>
            <li><a href="#sec-retencao"><span class="toc-num">7</span> Retenção</a></li>
            <li><a href="#sec-seguranca"><span class="toc-num">8</span> Segurança</a></li>
            <li><a href="#sec-direitos"><span class="toc-num">9</span> Seus Direitos</a></li>
            <li><a href="#sec-menores"><span class="toc-num">10</span> Menores de Idade</a></li>
            <li><a href="#sec-alteracoes"><span class="toc-num">11</span> Alterações</a></li>
            <li><a href="#sec-contato"><span class="toc-num">–</span> Contato / DPO</a></li>
          </ul>
          <div class="reg-toc-cta">
            <a href="mailto:contato@impactospositivos.com" class="btn btn-success w-100" style="border-radius:999px;font-weight:700;">
              <i class="bi bi-envelope me-1"></i> Falar com o DPO
            </a>
          </div>
        </nav>
      </aside>

      <!-- ── Conteúdo -->
      <div class="col-lg-9">

        <!-- Introdução -->
        <section id="sec-intro" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num" style="background:#CDDE00;color:#1E3425;">
              <i class="bi bi-shield-check" style="font-size:.85rem;"></i>
            </span>
            <h2 class="reg-section-title mb-0">Introdução</h2>
          </div>
          <p>
            A plataforma <strong>Impactos Positivos</strong>, operada pela <strong>Global Vision Access Comunicação e Marketing Ltda. (GVA)</strong>, está comprometida com a privacidade e a segurança dos dados pessoais de todos os seus usuários. Esta Política de Privacidade descreve como coletamos, usamos, armazenamos, compartilhamos e protegemos seus dados, em conformidade com a <strong>Lei Geral de Proteção de Dados Pessoais (LGPD — Lei nº 13.709/2018)</strong> e demais normas aplicáveis.
          </p>
          <p>
            Ao acessar ou utilizar nossa plataforma — incluindo o site <a href="https://www.impactospositivos.com" target="_blank" rel="noopener" class="reg-ext-link">www.impactospositivos.com</a>, o Prêmio Impactos Positivos e os serviços relacionados — você concorda com as práticas descritas nesta política. Recomendamos a leitura completa antes de prosseguir.
          </p>
        </section>

        <!-- 1. Quem Somos -->
        <section id="sec-quem-somos" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">1</span>
            <h2 class="reg-section-title mb-0">Quem Somos (Controlador dos Dados)</h2>
          </div>
          <p>O controlador dos seus dados pessoais é:</p>
          <div class="reg-promotora-box">
            <div class="row g-3">
              <div class="col-md-6">
                <p class="mb-1"><strong>Razão Social:</strong> Global Vision Access Comunicação e Marketing Ltda.</p>
                <p class="mb-1"><strong>Nome Fantasia:</strong> GVA / Impactos Positivos</p>
                <p class="mb-1"><strong>CNPJ:</strong> 08.817.535/0001-61</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>Endereço:</strong> Rua Apeninos, 429, cj. 1206 — Aclimação</p>
                <p class="mb-1"><strong>CEP:</strong> 01533-000 — São Paulo / SP</p>
                <p class="mb-1"><strong>E-mail:</strong> <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a></p>
              </div>
            </div>
          </div>
        </section>

        <!-- 2. Dados Coletados -->
        <section id="sec-dados-coletados" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">2</span>
            <h2 class="reg-section-title mb-0">Dados que Coletamos</h2>
          </div>
          <p>Coletamos os seguintes dados, conforme a interação que você realiza na plataforma:</p>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-person-vcard me-2"></i>Cadastro e inscrição</h5>
          <div class="reg-article">Nome completo, CPF, data de nascimento, e-mail, telefone/celular, gênero, formação acadêmica e endereço completo (incluindo CEP, rua, número, complemento, cidade e estado).</div>
          <div class="reg-article">Dados da pessoa jurídica: razão social, nome fantasia, CNPJ, município de atuação, redes sociais e site.</div>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-laptop me-2"></i>Dispositivo e acesso</h5>
          <div class="reg-article">Endereço IP, endereço MAC, modelo do dispositivo, sistema operacional, navegador utilizado, velocidade de conexão e dados de geolocalização aproximada.</div>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-globe me-2"></i>Navegação e comportamento</h5>
          <div class="reg-article">Cookies, páginas visitadas, buscas realizadas na plataforma, duração da visita, tipo de navegador e interações com conteúdos.</div>

          <h5 class="reg-fase-subtitulo mt-3"><i class="bi bi-hand-thumbs-up me-2"></i>Votação</h5>
          <div class="reg-article">Registros de votos realizados (popular, técnico e júri), vinculados ao seu cadastro, para fins de auditoria e prevenção a fraudes.</div>
        </section>

        <!-- 3. Finalidades -->
        <section id="sec-finalidades" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">3</span>
            <h2 class="reg-section-title mb-0">Para que Usamos seus Dados</h2>
          </div>
          <p>Seus dados são tratados para finalidades específicas, sem coleta em excesso ou sem propósito objetivo:</p>
          <div class="row g-3">
            <?php
            $finalidades = [
              ['bi-pencil-square',    'Inscrição no Prêmio',      'Processar e gerenciar sua inscrição no Prêmio Impactos Positivos.'],
              ['bi-headset',          'Atendimento',              'Responder dúvidas, suporte técnico e comunicações sobre sua participação.'],
              ['bi-megaphone',        'Comunicados',              'Envio de informações sobre fases, resultados e novidades da plataforma.'],
              ['bi-shield-exclamation','Prevenção a fraudes',     'Garantir a lisura do processo de votação e evitar usos irregulares.'],
              ['bi-bank',             'Obrigações legais',        'Cumprimento de determinações judiciais, regulatórias e fiscais.'],
              ['bi-graph-up',         'Pesquisa e ecossistema',   'Estudos sobre o ecossistema de impacto do Brasil, com dados anonimizados.'],
            ];
            foreach ($finalidades as [$icon, $titulo, $desc]): ?>
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

        <!-- 4. Bases Legais -->
        <section id="sec-bases-legais" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">4</span>
            <h2 class="reg-section-title mb-0">Bases Legais (LGPD)</h2>
          </div>
          <p>Tratamos seus dados com base nas seguintes hipóteses previstas na LGPD:</p>
          <div class="reg-article"><span class="reg-article-num">Art. 7º, I</span> <strong>Consentimento</strong> — para envio de comunicações de marketing e uso de cookies não essenciais.</div>
          <div class="reg-article"><span class="reg-article-num">Art. 7º, II</span> <strong>Cumprimento de obrigação legal ou regulatória</strong> — para atender exigências de órgãos públicos e judiciais.</div>
          <div class="reg-article"><span class="reg-article-num">Art. 7º, V</span> <strong>Execução de contrato ou procedimentos preliminares</strong> — para processar sua inscrição e participação no prêmio.</div>
          <div class="reg-article"><span class="reg-article-num">Art. 7º, IX</span> <strong>Legítimo interesse</strong> — para prevenção a fraudes, segurança da plataforma e melhoria dos serviços, sempre preservando seus direitos fundamentais.</div>
        </section>

        <!-- 5. Compartilhamento -->
        <section id="sec-compartilhamento" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">5</span>
            <h2 class="reg-section-title mb-0">Compartilhamento de Dados</h2>
          </div>
          <p>Não vendemos seus dados pessoais. Podemos compartilhá-los somente com:</p>
          <div class="reg-article">
            <span class="reg-article-num">5.1</span>
            <strong>Prestadores de serviço:</strong> empresas que nos auxiliam na operação da plataforma (hospedagem, e-mail transacional, análise de tráfego), vinculadas por contratos de confidencialidade e obrigações compatíveis com a LGPD.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">5.2</span>
            <strong>Órgãos e autoridades:</strong> quando exigido por lei, decisão judicial ou solicitação de autoridades competentes (ANPD, Ministério Público, poder judiciário).
          </div>
          <div class="reg-article">
            <span class="reg-article-num">5.3</span>
            <strong>Parceiros do ecossistema:</strong> patrocinadores, apoiadores e parceiros institucionais do Prêmio, exclusivamente para finalidades relacionadas à premiação e ao ecossistema de impacto, mediante seu prévio consentimento quando aplicável.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">5.4</span>
            Não nos responsabilizamos pela coleta ou tratamento de dados em sites de terceiros para os quais haja redirecionamento a partir da nossa plataforma.
          </div>
        </section>

        <!-- 6. Cookies -->
        <section id="sec-cookies" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">6</span>
            <h2 class="reg-section-title mb-0">Cookies e Tecnologias Similares</h2>
          </div>
          <p>Utilizamos cookies para melhorar sua experiência na plataforma. Os tipos de cookies utilizados são:</p>
          <div class="reg-article"><span class="reg-article-num">Essenciais</span> Necessários para o funcionamento básico da plataforma (sessão de login, preferências de segurança). Não podem ser desativados.</div>
          <div class="reg-article"><span class="reg-article-num">Analíticos</span> Coletam dados sobre como os usuários navegam na plataforma (ex.: Google Analytics), para melhorar nossos serviços. Requerem seu consentimento.</div>
          <div class="reg-article"><span class="reg-article-num">Funcionais</span> Lembram suas preferências (idioma, configurações de exibição) para personalizar sua experiência.</div>
          <p class="mt-3">Você pode gerenciar ou desativar cookies não essenciais pelas configurações do seu navegador. A desativação pode afetar algumas funcionalidades da plataforma.</p>
        </section>

        <!-- 7. Retenção -->
        <section id="sec-retencao" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">7</span>
            <h2 class="reg-section-title mb-0">Retenção e Exclusão de Dados</h2>
          </div>
          <div class="reg-article"><span class="reg-article-num">7.1</span> Mantemos seus dados pelo tempo necessário para cumprir as finalidades descritas nesta política ou obrigações legais aplicáveis.</div>
          <div class="reg-article"><span class="reg-article-num">7.2</span> Dados de inscrição e participação no Prêmio são mantidos por até <strong>5 anos</strong> após o encerramento de cada edição, para fins de registro histórico e cumprimento de obrigações legais.</div>
          <div class="reg-article"><span class="reg-article-num">7.3</span> Após o período de retenção, os dados são anonimizados ou excluídos de forma segura.</div>
          <div class="reg-article"><span class="reg-article-num">7.4</span> Você pode solicitar a exclusão antecipada dos seus dados conforme descrito na seção de Direitos do Titular (seção 9).</div>
        </section>

        <!-- 8. Segurança -->
        <section id="sec-seguranca" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">8</span>
            <h2 class="reg-section-title mb-0">Segurança dos Dados</h2>
          </div>
          <div class="reg-article"><span class="reg-article-num">8.1</span> Adotamos medidas técnicas e organizacionais adequadas para proteger seus dados contra acesso não autorizado, perda, destruição ou alteração indevida.</div>
          <div class="reg-article"><span class="reg-article-num">8.2</span> Nossas medidas incluem: controle de acesso por autenticação, criptografia de dados sensíveis em trânsito (HTTPS/TLS) e em repouso, monitoramento de acessos e backups regulares.</div>
          <div class="reg-article"><span class="reg-article-num">8.3</span> Em caso de incidente de segurança que possa acarretar risco ou dano relevante aos titulares, comunicaremos a ANPD e os afetados nos prazos previstos pela LGPD.</div>
        </section>

        <!-- 9. Direitos -->
        <section id="sec-direitos" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">9</span>
            <h2 class="reg-section-title mb-0">Seus Direitos como Titular</h2>
          </div>
          <p>Nos termos da LGPD, você tem os seguintes direitos sobre seus dados pessoais:</p>
          <div class="row g-3">
            <?php
            $direitos = [
              ['bi-eye',              'Acesso',           'Confirmar a existência e acessar seus dados tratados por nós.'],
              ['bi-pencil',           'Correção',         'Solicitar a correção de dados incompletos, inexatos ou desatualizados.'],
              ['bi-trash',            'Eliminação',       'Solicitar a exclusão dos dados tratados com base no seu consentimento.'],
              ['bi-x-circle',         'Revogação',        'Revogar o consentimento a qualquer momento, sem prejuízo do tratamento realizado anteriormente.'],
              ['bi-info-circle',      'Informação',       'Ser informado sobre as entidades públicas e privadas com as quais compartilhamos seus dados.'],
              ['bi-file-earmark-text','Portabilidade',    'Solicitar a portabilidade dos seus dados a outro fornecedor de serviço.'],
              ['bi-dash-circle',      'Oposição',         'Opor-se ao tratamento realizado com base em legítimo interesse.'],
              ['bi-slash-circle',     'Bloqueio/Anonimização', 'Solicitar o bloqueio ou anonimização de dados desnecessários, excessivos ou tratados em desconformidade.'],
            ];
            foreach ($direitos as [$icon, $titulo, $desc]): ?>
              <div class="col-md-6">
                <div class="reg-board-card">
                  <div class="reg-board-avatar" style="background:#1E3425;"><i class="bi <?= $icon ?>"></i></div>
                  <div>
                    <div class="reg-board-name"><?= $titulo ?></div>
                    <div class="reg-board-role"><?= $desc ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="reg-alert-atencao d-flex gap-3 align-items-start mt-4">
            <i class="bi bi-envelope-fill fs-5 flex-shrink-0 mt-1"></i>
            <div>
              Para exercer qualquer um dos seus direitos, envie uma solicitação para <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a>. Responderemos em até <strong>15 dias úteis</strong>, conforme previsto na LGPD.
            </div>
          </div>
        </section>

        <!-- 10. Menores -->
        <section id="sec-menores" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">10</span>
            <h2 class="reg-section-title mb-0">Menores de Idade</h2>
          </div>
          <div class="reg-article"><span class="reg-article-num">10.1</span> Nossa plataforma não é direcionada a menores de 18 anos para fins de inscrição no Prêmio.</div>
          <div class="reg-article"><span class="reg-article-num">10.2</span> A participação de menores de idade é de exclusiva responsabilidade dos pais ou responsáveis legais, que devem fornecer consentimento específico e em destaque, nos termos do art. 14 da LGPD.</div>
          <div class="reg-article"><span class="reg-article-num">10.3</span> Caso identifiquemos que dados de menores foram coletados sem o devido consentimento, procederemos à exclusão imediata.</div>
        </section>

        <!-- 11. Alterações -->
        <section id="sec-alteracoes" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">11</span>
            <h2 class="reg-section-title mb-0">Alterações desta Política</h2>
          </div>
          <div class="reg-article"><span class="reg-article-num">11.1</span> Esta Política de Privacidade pode ser atualizada periodicamente para refletir mudanças legais, operacionais ou de serviço.</div>
          <div class="reg-article"><span class="reg-article-num">11.2</span> Alterações significativas serão comunicadas por e-mail ou por aviso destacado na plataforma com antecedência mínima de 10 dias.</div>
          <div class="reg-article"><span class="reg-article-num">11.3</span> A continuidade do uso da plataforma após a notificação implica a aceitação da versão atualizada.</div>
          <div class="reg-article"><span class="reg-article-num">11.4</span> A versão em vigor é sempre a disponível em <a href="https://www.impactospositivos.com/politica-de-privacidade" target="_blank" rel="noopener" class="reg-ext-link">impactospositivos.com/politica-de-privacidade</a>.</div>
        </section>

        <!-- Contato / DPO -->
        <section id="sec-contato" class="reg-section" style="border-bottom:0;">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num" style="background:#95BCCC;color:#1E3425;">
              <i class="bi bi-person-badge" style="font-size:.85rem;"></i>
            </span>
            <h2 class="reg-section-title mb-0">Contato e Encarregado de Dados (DPO)</h2>
          </div>
          <p>Em caso de dúvidas, solicitações ou reclamações relacionadas ao tratamento dos seus dados pessoais, entre em contato com nosso Encarregado de Proteção de Dados (DPO):</p>
          <div class="reg-promotora-box">
            <div class="row g-3">
              <div class="col-md-6">
                <p class="mb-1"><strong>Empresa:</strong> Global Vision Access Comunicação e Marketing Ltda.</p>
                <p class="mb-1"><strong>E-mail:</strong> <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a></p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>Endereço:</strong> Rua Apeninos, 429, cj. 1206 — Aclimação</p>
                <p class="mb-1"><strong>CEP:</strong> 01533-000 — São Paulo / SP</p>
              </div>
            </div>
          </div>
          <p class="mt-3" style="font-size:.82rem;color:#6c8070;">
            Você também pode protocolar reclamações perante a <strong>Autoridade Nacional de Proteção de Dados (ANPD)</strong> em <a href="https://www.gov.br/anpd" target="_blank" rel="noopener" class="reg-ext-link">gov.br/anpd</a>.
          </p>
        </section>

        <!-- CTA final -->
        <div class="cta-box mt-5">
          <div class="row align-items-center g-3">
            <div class="col-md-8">
              <h3 class="cta-title mb-1"><i class="bi bi-shield-check me-2"></i>Dúvidas sobre seus dados?</h3>
              <p class="cta-sub mb-0">Entre em contato com nosso DPO. Respondemos em até 15 dias úteis.</p>
            </div>
            <div class="col-md-4 text-md-end">
              <a href="mailto:contato@impactospositivos.com" class="btn-cta-parceiro">
                <i class="bi bi-envelope me-2"></i> Falar com o DPO
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
  /* Scroll-spy: destaca o item do índice conforme seção visível */
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
      links.forEach(function (l) {
        l.classList.remove('reg-toc-link--active');
      });
      if (current) {
        const match = targets.find(function (t) { return t.el === current; });
        if (match) match.link.classList.add('reg-toc-link--active');
      }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
  })();
</script>

<?php include __DIR__ . '/app/views/public/footer_public.php'; ?>