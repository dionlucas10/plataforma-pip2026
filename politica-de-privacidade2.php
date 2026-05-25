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

<!-- HERO -->
<section class="reg-hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-8">
        <span class="reg-update-badge mb-3 d-inline-flex">
          <i class="bi bi-patch-check-fill me-1"></i>
          Última atualização: 04 de junho de 2024
        </span>
        <h1 class="reg-hero-title mb-3">
          Política de<br>
          <span style="color:#CDDE00;">Privacidade</span>
        </h1>
        <p class="reg-hero-sub mb-4">
          Saiba como coletamos, usamos e protegemos seus dados pessoais na plataforma Impactos Positivos, em conformidade com a Lei Geral de Proteção de Dados (LGPD — Lei nº 13.709/2018).
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="#sec-coleta" class="btn-premiacao-outline">
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


<!-- CONTEÚDO PRINCIPAL -->
<section class="py-5">
  <div class="container">
    <div class="row g-5">

      <!-- Índice lateral -->
      <aside class="col-lg-3 d-none d-lg-block">
        <nav class="reg-toc" aria-label="Índice da política de privacidade">
          <div class="reg-toc-title"><i class="bi bi-list-ul me-1"></i> Índice</div>
          <ul class="reg-toc-list">
            <li><a href="#sec-coleta"><span class="toc-num">1</span> Coleta e Uso de Dados</a></li>
            <li><a href="#sec-dados-tabela"><span class="toc-num">–</span> Dados Coletados</a></li>
            <li><a href="#sec-finalidades"><span class="toc-num">–</span> Finalidades</a></li>
            <li><a href="#sec-compartilhamento"><span class="toc-num">–</span> Compartilhamento</a></li>
            <li><a href="#sec-geral"><span class="toc-num">2</span> Disposições Gerais</a></li>
            <li><a href="#sec-promotora"><span class="toc-num">–</span> Promotora</a></li>
          </ul>
          <div class="reg-toc-cta">
            <a href="mailto:contato@impactospositivos.com" class="btn btn-success w-100" style="border-radius:999px;font-weight:700;">
              <i class="bi bi-envelope me-1"></i> Falar com o DPO
            </a>
          </div>
        </nav>
      </aside>

      <!-- Conteúdo -->
      <div class="col-lg-9">

        <!-- 1. Coleta e Uso de Dados Pessoais -->
        <section id="sec-coleta" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">1</span>
            <h2 class="reg-section-title mb-0">Coleta e Uso de Dados Pessoais</h2>
          </div>

          <div class="reg-article">
            <span class="reg-article-num">1.1</span>
            Estamos comprometidos com as disposições trazidas pela <strong>Lei Geral de Proteção de Dados (“LGPD”)</strong>, razão pela qual demonstramos neste tópico como os dados são tratados, para quais finalidades são coletados e como exercer os direitos previstos na legislação.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">1.2</span>
            Assim, para se inscrever no concurso, seja você votante, jurado ou competidor, será necessário informar, além dos dados da pessoa jurídica (a depender da finalidade do seu cadastro), os dados pessoais do representante da entidade. Reforçamos que este tópico se refere ao tratamento dos dados pessoais quando inseridos pelo link <a href="https://www.impactospositivos.com" target="_blank" rel="noopener" class="reg-ext-link">www.impactospositivos.com</a>, bem como quanto ao compartilhamento de dados pessoais com outros agentes de tratamento.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">1.3</span>
            Nestes casos, será de nossa responsabilidade selecionar adequadamente as bases legais que se relacionam com a coleta dos dados pessoais, além de atender de forma direta às solicitações quanto aos direitos previstos na LGPD.
          </div>

          <div class="reg-article" id="sec-dados-tabela">
            <span class="reg-article-num">1.4</span>
            Os seguintes dados poderão ser coletados:
          </div>

          <!-- Tabela de dados coletados -->
          <div class="table-responsive mt-3 mb-4">
            <table class="table table-bordered align-middle" style="font-size:.9rem;">
              <thead style="background:#1E3425;color:#fff;">
                <tr>
                  <th style="width:35%;">Conjunto de dados</th>
                  <th>Dados pessoais</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Dados de cadastro do representante da pessoa jurídica — inscrição</strong></td>
                  <td>Nome completo, CPF, data de nascimento, e-mail, telefone, gênero, formação acadêmica, endereço completo.</td>
                </tr>
                <tr>
                  <td><strong>Dados de identificação, geolocalização e configuração do dispositivo — website</strong></td>
                  <td>Identificadores dos seus dispositivos eletrônicos, como o endereço de IP do seu computador ou o endereço MAC do seu celular, bem como modelo, fabricante, sistema operacional, operadora de telefonia, tipo de navegador e velocidade da conexão. Geolocalização e identificador anonimizado do aparelho celular.</td>
                </tr>
                <tr>
                  <td><strong>Dados de navegação — usuários do website</strong></td>
                  <td>Dados coletados por meio de cookies, páginas visitadas no nosso site, informação que você busca/procura, duração da sua visita, localização geográfica, tipo de navegador, duração da visita e páginas visitadas.</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="reg-article" id="sec-finalidades">
            <span class="reg-article-num">1.5</span>
            Os dados pessoais serão coletados para finalidades específicas, evitando a coleta em excesso e/ou tratamento dos dados para fins genéricos e sem propósitos objetivos.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">1.6</span>
            Assim, coletaremos dados pessoais para que seja possível a <strong>inscrição no Prêmio Impactos Positivos</strong>, atendimento e suporte aos inscritos, informar sobre novidades, funcionalidades, conteúdos, notícias e promoções relevantes, para o cumprimento de determinações judiciais, prevenção a fraudes, bem como para registrar o acesso à plataforma, pois temos o dever legal de armazenar algumas de suas informações (como IP, data e hora de acesso) para eventualmente fornecê-las a autoridades judiciais.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">1.7</span>
            É muito importante que você entenda que, quando houver o redirecionamento de conteúdo disponível para sites de terceiros, através do nosso website, não nos responsabilizamos pela coleta e finalidades para o tratamento dos dados realizado nestes ambientes, cabendo a você, usuário, checar a Política de Privacidade específica destes terceiros.
          </div>

          <div class="reg-article" id="sec-compartilhamento">
            <span class="reg-article-num">1.8</span>
            Listamos abaixo algumas categorias de destinatário com as quais podemos compartilhar os dados pessoais:
          </div>
          <ul class="mt-2 mb-3" style="padding-left:1.5rem;line-height:1.8;">
            <li>Empresas que fornecem acesso aos nossos serviços e nos auxiliam no fornecimento, bem como possam tomar providências em nosso nome, protegendo nossos direitos, usuários, sistemas e serviços;</li>
            <li>Terceiros com os quais devemos cumprir com as obrigações legais (exemplo: órgãos governamentais, agências reguladoras, a própria ANPD, autoridade responsável pela fiscalização da LGPD, poder judiciário e outras autoridades públicas);</li>
            <li>Parceiros, patrocinadores, apoiadores, incentivadores do projeto e organizações, que nos auxiliam nos estudos sobre o ecossistema de impacto do Brasil, a fim de aprimorar a premiação anualmente.</li>
          </ul>

          <div class="reg-article">
            <span class="reg-article-num">1.9</span>
            Para exercer os seus direitos previstos da LGPD, nos envie um e-mail para <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a>.
          </div>
        </section>

        <!-- 2. Disposições Gerais -->
        <section id="sec-geral" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">2</span>
            <h2 class="reg-section-title mb-0">Disposições Gerais</h2>
          </div>

          <div class="reg-article">
            <span class="reg-article-num">2.1</span>
            Para viabilizar o projeto, contaremos com a participação e o comprometimento de diferentes apoiadores, que irão promovê-la em suas plataformas e redes sociais. É importante destacar que não há qualquer relação contratual entre o Projeto Impactos Positivos e esses voluntários.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">2.2</span>
            É importante que você leia, entenda e esteja de acordo com esta política antes de seguir com a sua inscrição/votação.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">2.3</span>
            A equipe de tecnologia responsável pelo prêmio irá fornecer suporte em caso de eventuais falhas nas funcionalidades de cadastro e votação do sistema. No entanto, é importante ressaltar que instabilidades no sistema podem ocorrer devido a altos níveis de acesso à plataforma, e que, portanto, não haverá prorrogação dos prazos da premiação ou compensação para os afetados.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">2.4</span>
            O participante concorda expressamente, pelo simples ato de inscrição, votação e participação, que a empresa promotora, seus diretores ou empregados, assim como os de suas agências, não serão responsáveis por qualquer dano ou prejuízo oriundo da aceitação do prêmio, de sua participação no concurso, assim como de qualquer problema externo ou de força maior que possa impossibilitar a participação no concurso e/ou no recebimento do prêmio.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">2.5</span>
            A empresa promotora se reserva o direito de, na eventualidade deste concurso não poder ocorrer por qualquer razão, adiá-lo, modificá-lo ou alterá-lo a fim de garantir a lisura e correção do concurso, fazendo a respectiva divulgação no site <a href="https://impactospositivos.com" target="_blank" rel="noopener" class="reg-ext-link">impactospositivos.com</a>.
          </div>

          <div class="reg-article">
            <span class="reg-article-num">2.6</span>
            As decisões da empresa promotora são e serão finais e irrecorríveis.
          </div>
        </section>

        <!-- Promotora -->
        <section id="sec-promotora" class="reg-section" style="border-bottom:0;">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num" style="background:#95BCCC;color:#1E3425;">
              <i class="bi bi-building" style="font-size:.85rem;"></i>
            </span>
            <h2 class="reg-section-title mb-0">Equipe Impactos Positivos</h2>
          </div>
          <div class="reg-promotora-box">
            <div class="row g-3">
              <div class="col-md-6">
                <p class="mb-1"><strong>Razão Social:</strong> Global Vision Access Comunicação e Marketing Ltda.</p>
                <p class="mb-1"><strong>Nome Fantasia:</strong> GVA / Impactos Positivos</p>
                <p class="mb-1"><strong>CNPJ:</strong> 08.817.535/0001-61</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>E-mail:</strong> <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a></p>
                <p class="mb-1"><strong>Endereço:</strong> Rua Apeninos, 429, cj. 1206 — Aclimação, São Paulo / SP</p>
                <p class="mb-0"><strong>Última atualização:</strong> 04 de junho de 2024.</p>
              </div>
            </div>
          </div>
        </section>

        <!-- CTA final -->
        <div class="cta-box mt-5">
          <div class="row align-items-center g-3">
            <div class="col-md-8">
              <h3 class="cta-title mb-1"><i class="bi bi-shield-check me-2"></i>Dúvidas sobre seus dados?</h3>
              <p class="cta-sub mb-0">Entre em contato com nossa equipe. Respondemos em até 15 dias úteis.</p>
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