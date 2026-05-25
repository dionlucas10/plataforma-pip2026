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

$pageTitle = 'Termos de Uso | Impactos Positivos';
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
          Termos de<br>
          <span style="color:#CDDE00;">Uso</span>
        </h1>
        <p class="reg-hero-sub mb-4">
          Leia com atenção as regras e diretrizes que regem o uso da Plataforma Impactos Positivos. Ao se cadastrar, você concorda integralmente com estes termos.
        </p>
        <div class="d-flex flex-wrap gap-2">
          <a href="#sec-intro" class="btn-premiacao-outline">
            <i class="bi bi-file-text me-2"></i> Ler termos completos
          </a>
          <a href="mailto:contato@impactospositivos.com" class="btn-premiacao-outline">
            <i class="bi bi-envelope me-2"></i> Falar conosco
          </a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="d-flex flex-column gap-2">
          <div class="reg-hero-info-item">
            <i class="bi bi-people" style="color:#CDDE00;"></i>
            Válido para <strong>todos os usuários</strong>
          </div>
          <div class="reg-hero-info-item">
            <i class="bi bi-trophy" style="color:#CDDE00;"></i>
            Inclui regras do <strong>Prêmio IP 2026</strong>
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
        <nav class="reg-toc" aria-label="Índice dos termos de uso">
          <div class="reg-toc-title"><i class="bi bi-list-ul me-1"></i> Índice</div>
          <ul class="reg-toc-list">
            <li><a href="#sec-intro"><span class="toc-num">–</span> Introdução</a></li>
            <li><a href="#sec-aceite"><span class="toc-num">1</span> Aceite dos Termos</a></li>
            <li><a href="#sec-cadastro"><span class="toc-num">2</span> Cadastro e Conta</a></li>
            <li><a href="#sec-conduta"><span class="toc-num">3</span> Conduta do Usuário</a></li>
            <li><a href="#sec-conteudo"><span class="toc-num">4</span> Contribuição de Conteúdo</a></li>
            <li><a href="#sec-inscricao"><span class="toc-num">5</span> Inscrição no Prêmio</a></li>
            <li><a href="#sec-votacao"><span class="toc-num">6</span> Votação</a></li>
            <li><a href="#sec-moderacao"><span class="toc-num">7</span> Moderação</a></li>
            <li><a href="#sec-conta"><span class="toc-num">8</span> Suspensão de Conta</a></li>
            <li><a href="#sec-atualizacoes"><span class="toc-num">9</span> Atualizações</a></li>
            <li><a href="#sec-contato"><span class="toc-num">–</span> Contato</a></li>
          </ul>
          <div class="reg-toc-cta">
            <a href="empreendedores/register.php" class="btn btn-success w-100" style="border-radius:999px;font-weight:700;">
              <i class="bi bi-pencil-square me-1"></i> Inscreva-se
            </a>
          </div>
        </nav>
      </aside>

      <!-- Conteúdo -->
      <div class="col-lg-9">

        <!-- Introdução -->
        <section id="sec-intro" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num" style="background:#CDDE00;color:#1E3425;">
              <i class="bi bi-file-text" style="font-size:.85rem;"></i>
            </span>
            <h2 class="reg-section-title mb-0">Introdução</h2>
          </div>
          <p>
            Bem-vindo(a) à <strong>Plataforma Impactos Positivos</strong>! Estamos comprometidos em promover um ambiente online seguro, colaborativo e positivo para todos os nossos usuários — empreendedores inscritos, votantes, jurados e visitantes.
          </p>
          <p>
            Estes Termos de Uso definem as regras e diretrizes que todos os membros da nossa comunidade devem seguir para garantir uma experiência respeitosa e justa. Eles se aplicam a todas as interações realizadas na plataforma, incluindo cadastro, inscrição no Prêmio Impactos Positivos 2026, votação popular, participação como jurado e navegação geral.
          </p>
        </section>

        <!-- 1. Aceite -->
        <section id="sec-aceite" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">1</span>
            <h2 class="reg-section-title mb-0">Aceite dos Termos</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">1.1</span>
            Ao se cadastrar e utilizar a Plataforma Impactos Positivos, você concorda em cumprir estes Termos de Uso, bem como nossa <a href="politica-de-privacidade.php" class="reg-ext-link">Política de Privacidade</a>.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">1.2</span>
            Se você não concorda com qualquer parte destes termos, solicitamos que não utilize nossa plataforma.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">1.3</span>
            O simples ato de realizar cadastro, votar, se inscrever no Prêmio ou acessar áreas restritas da plataforma implica aceitação integral e irrestrita destes termos.
          </div>
        </section>

        <!-- 2. Cadastro -->
        <section id="sec-cadastro" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">2</span>
            <h2 class="reg-section-title mb-0">Cadastro e Conta</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">2.1</span>
            Para acessar funcionalidades como votação e inscrição no Prêmio, é necessário criar uma conta na plataforma, fornecendo informações verdadeiras, completas e atualizadas.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">2.2</span>
            Você é o único responsável pela confidencialidade das suas credenciais de acesso (e-mail e senha). Não compartilhe sua senha com terceiros.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">2.3</span>
            É proibido criar contas falsas, duplicadas, temporárias ou com informações inverdicas. Contas irregulares serão removidas sem aviso prévio.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">2.4</span>
            Cada usuário deve possuir apenas uma conta ativa na plataforma. Contas adicionais criadas com o objetivo de inflar votos ou burlar regras serão imediatamente desativadas.
          </div>
        </section>

        <!-- 3. Conduta -->
        <section id="sec-conduta" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">3</span>
            <h2 class="reg-section-title mb-0">Conduta do Usuário</h2>
          </div>

          <h5 class="reg-fase-subtitulo"><i class="bi bi-hand-thumbs-up me-2"></i>3.1 Respeito e Cortesia</h5>
          <div class="reg-article">Trate todos os usuários com respeito e cortesia, independentemente de opiniões, origens ou categorias de participação.</div>
          <div class="reg-article">Evite linguagem ofensiva, assédio, discriminação ou qualquer forma de comportamento que possa ser considerado prejudicial ou intimidatório.</div>

          <h5 class="reg-fase-subtitulo mt-4"><i class="bi bi-slash-circle me-2"></i>3.2 Conteúdo Proibido</h5>
          <div class="reg-article">Não publique, compartilhe ou promova conteúdo que seja ilegal, ofensivo, difamatório, obsceno ou que viole os direitos de terceiros.</div>
          <div class="reg-article">Conteúdos que promovam violência, ódio, discriminação ou qualquer forma de atividade ilegal são estritamente proibidos e sujeitos a remoção imediata e encerramento de conta.</div>

          <h5 class="reg-fase-subtitulo mt-4"><i class="bi bi-c-circle me-2"></i>3.3 Direitos Autorais e Propriedade Intelectual</h5>
          <div class="reg-article">Respeite os direitos autorais e de propriedade intelectual de outros. Não publique ou compartilhe conteúdo para o qual você não possui as devidas permissões.</div>
          <div class="reg-article">Ao compartilhar conteúdo de terceiros, certifique-se de dar os devidos créditos e obter as autorizações necessárias.</div>

          <h5 class="reg-fase-subtitulo mt-4"><i class="bi bi-shield-lock me-2"></i>3.4 Segurança e Privacidade</h5>
          <div class="reg-article">Não compartilhe informações pessoais de outros usuários sem o seu consentimento explícito.</div>
          <div class="reg-article">Proteja suas próprias informações pessoais e não divulgue senhas ou detalhes confidenciais na plataforma.</div>
        </section>

        <!-- 4. Conteúdo -->
        <section id="sec-conteudo" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">4</span>
            <h2 class="reg-section-title mb-0">Contribuição de Conteúdo</h2>
          </div>

          <h5 class="reg-fase-subtitulo"><i class="bi bi-person-check me-2"></i>4.1 Responsabilidade pelo Conteúdo</h5>
          <div class="reg-article">Você é integralmente responsável por todo o conteúdo que publica na plataforma, incluindo textos, imagens, vídeos, links e demais materiais.</div>
          <div class="reg-article">Assegure-se de que todo o conteúdo compartilhado é verdadeiro, preciso e não engana ou desinforma outros usuários ou avaliadores.</div>

          <h5 class="reg-fase-subtitulo mt-4"><i class="bi bi-camera me-2"></i>4.2 Consentimento para Uso de Imagem</h5>
          <div class="reg-article">Ao publicar imagens, vídeos ou outros materiais visuais na plataforma, você concede à Plataforma Impactos Positivos o direito de usar, reproduzir e exibir esses materiais para fins promocionais e institucionais — incluindo divulgação nas redes sociais do Prêmio e matérias na mídia parceira.</div>
          <div class="reg-article">Essa autorização é válida pelo período de vigência da edição do Prêmio e pode ser revogada mediante solicitação formal pelo e-mail <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a>.
          </div>

          <h5 class="reg-fase-subtitulo mt-4"><i class="bi bi-check2-square me-2"></i>4.3 Veracidade das Informações</h5>
          <div class="reg-article">Todas as informações fornecidas no formulário de inscrição, perfil do negócio e demais campos da plataforma devem ser verdadeiras e atualizadas.</div>
          <div class="reg-article">A inclusão de dados falsos, distorcidos ou enganosos pode resultar em desclassificação imediata do Prêmio e encerramento da conta.</div>
        </section>

        <!-- 5. Inscrição -->
        <section id="sec-inscricao" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">5</span>
            <h2 class="reg-section-title mb-0">Inscrição no Prêmio</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">5.1</span>
            A inscrição no Prêmio Impactos Positivos 2026 está disponível para pessoas jurídicas que atendam aos critérios descritos no <a href="regulamento-do-premio.php" class="reg-ext-link">Regulamento do Prêmio</a>.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">5.2</span>
            Cada CNPJ pode possuir apenas uma inscrição ativa por edição. Inscrições duplicadas serão removidas sem aviso prévio.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">5.3</span>
            Ao finalizar a inscrição, o representante autoriza a plataforma a verificar a regularidade da empresa em fontes públicas (TST, IBAMA e outras), conforme descrito no Regulamento.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">5.4</span>
            O vídeo e as fotos enviadas no momento da inscrição poderão ser utilizados pela Plataforma Impactos Positivos para divulgação no site, redes sociais e materiais de comunicação do Prêmio.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">5.5</span>
            Inscrições incompletas, incorretas ou em desconformidade com o Regulamento serão automaticamente desclassificadas.
          </div>
        </section>

        <!-- 6. Votação -->
        <section id="sec-votacao" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">6</span>
            <h2 class="reg-section-title mb-0">Votação</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">6.1</span>
            A votação popular é aberta a qualquer usuário cadastrado e verificado na plataforma. Cada usuário pode votar uma vez por negócio, podendo votar em múltiplos negócios dentro das regras de cada fase.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">6.2</span>
            É estritamente <strong>proibido o uso de meios automatizados</strong> (robôs, scripts, bots ou qualquer outro recurso similar) para inflar ou manipular votos. A identificação de tal prática resultará na anulação dos votos, notificação ao inscrito e, em caso de reincidência, desclassificação do concurso.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">6.3</span>
            Não serão aceitos votos provenientes de e-mails secundários criados artificialmente, e-mails temporários, falsos ou que não permitam contato direto com o votante.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">6.4</span>
            A plataforma poderá realizar auditorias e análises a qualquer momento para garantir a integridade do processo de votação.
          </div>
        </section>

        <!-- 7. Moderação -->
        <section id="sec-moderacao" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">7</span>
            <h2 class="reg-section-title mb-0">Moderação e Remoção de Conteúdo</h2>
          </div>

          <h5 class="reg-fase-subtitulo"><i class="bi bi-shield-check me-2"></i>7.1 Diretrizes de Moderação</h5>
          <div class="reg-article">A equipe da Plataforma Impactos Positivos se reserva o direito de moderar, editar ou remover qualquer conteúdo que viole estes Termos de Uso, sem necessidade de aviso prévio.</div>
          <div class="reg-article">Usuários que violarem repetidamente nossas diretrizes poderão ter suas contas suspensas ou encerradas definitivamente.</div>

          <h5 class="reg-fase-subtitulo mt-4"><i class="bi bi-flag me-2"></i>7.2 Relato de Abusos</h5>
          <div class="reg-article">Se você encontrar conteúdo ou comportamento que viole estes termos, reporte imediatamente à nossa equipe pelo e-mail <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a>.</div>
          <div class="reg-article">Denúncias serão analisadas em até 10 dias úteis, garantindo ampla defesa e contraditório ao denunciado. Todo o processo é conduzido de forma confidencial.</div>
        </section>

        <!-- 8. Suspensão de Conta -->
        <section id="sec-conta" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">8</span>
            <h2 class="reg-section-title mb-0">Suspensão e Encerramento de Conta</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">8.1</span>
            A Plataforma Impactos Positivos se reserva o direito de suspender ou encerrar sua conta a qualquer momento, sem aviso prévio, nos seguintes casos:
          </div>
          <ul class="mt-2 mb-3" style="padding-left:1.5rem;line-height:1.8;">
            <li>Violação de qualquer item destes Termos de Uso ou do Regulamento do Prêmio;</li>
            <li>Uso de meios fraudulentos para votar ou manipular resultados;</li>
            <li>Fornecimento de informações falsas no cadastro ou inscrição;</li>
            <li>Comportamento prejudicial à comunidade, a outros usuários ou à integridade da plataforma;</li>
            <li>Qualquer atividade que viole a legislação vigente.</li>
          </ul>
          <div class="reg-article">
            <span class="reg-article-num">8.2</span>
            O encerramento da conta implica a perda do acesso a todas as funcionalidades da plataforma, incluindo inscrições e votos registrados.
          </div>
        </section>

        <!-- 9. Atualizações -->
        <section id="sec-atualizacoes" class="reg-section">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num">9</span>
            <h2 class="reg-section-title mb-0">Revisões e Atualizações</h2>
          </div>
          <div class="reg-article">
            <span class="reg-article-num">9.1</span>
            Estes Termos de Uso serão revisados periodicamente para garantir sua eficácia e conformidade com as leis aplicáveis, incluindo a LGPD e o Marco Civil da Internet.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">9.2</span>
            Atualizações significativas serão comunicadas a todos os usuários cadastrados por e-mail e/ou por aviso destacado na plataforma, com antecedência mínima de 10 dias.
          </div>
          <div class="reg-article">
            <span class="reg-article-num">9.3</span>
            A continuidade do uso da plataforma após a notificação implica aceitação da versão atualizada. A versão em vigor é sempre a disponível em <a href="https://www.impactospositivos.com/termos-de-uso" target="_blank" rel="noopener" class="reg-ext-link">impactospositivos.com/termos-de-uso</a>.
          </div>
        </section>

        <!-- Contato -->
        <section id="sec-contato" class="reg-section" style="border-bottom:0;">
          <div class="d-flex align-items-start gap-3 mb-3">
            <span class="reg-section-num" style="background:#95BCCC;color:#1E3425;">
              <i class="bi bi-envelope" style="font-size:.85rem;"></i>
            </span>
            <h2 class="reg-section-title mb-0">Contato</h2>
          </div>
          <p>
            Para dúvidas, comentários ou preocupações sobre estes Termos de Uso, entre em contato com a nossa equipe:
          </p>
          <div class="reg-promotora-box">
            <div class="row g-3">
              <div class="col-md-6">
                <p class="mb-1"><strong>Plataforma:</strong> Impactos Positivos</p>
                <p class="mb-1"><strong>Empresa:</strong> Global Vision Access Comunicação e Marketing Ltda. (GVA)</p>
                <p class="mb-1"><strong>CNPJ:</strong> 08.817.535/0001-61</p>
              </div>
              <div class="col-md-6">
                <p class="mb-1"><strong>E-mail:</strong> <a href="mailto:contato@impactospositivos.com" class="reg-ext-link">contato@impactospositivos.com</a></p>
                <p class="mb-1"><strong>Endereço:</strong> Rua Apeninos, 429, cj. 1206 — Aclimação, São Paulo / SP</p>
                <p class="mb-0"><strong>Última atualização:</strong> maio de 2026.</p>
              </div>
            </div>
          </div>
          <p class="mt-4">Agradecemos por fazer parte da nossa comunidade e por contribuir para um ambiente online positivo e colaborativo.</p>
          <p style="color:#6c8070;"><em>Atenciosamente, <strong>Equipe Impactos Positivos</strong></em></p>
        </section>

        <!-- CTA final -->
        <div class="cta-box mt-5">
          <div class="row align-items-center g-3">
            <div class="col-md-8">
              <h3 class="cta-title mb-1"><i class="bi bi-trophy-fill me-2"></i>Pronto para se inscrever?</h3>
              <p class="cta-sub mb-0">Inscrições abertas de 11/05 a 24/07/2026. Faça parte do Prêmio Impactos Positivos 2026!</p>
            </div>
            <div class="col-md-4 text-md-end">
              <a href="empreendedores/register.php" class="btn-cta-parceiro">
                <i class="bi bi-pencil-square me-2"></i> Inscreva-se agora
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