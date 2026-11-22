(() => {
  const root = document.getElementById('mrstock-public-map');
  const content = root.querySelector('.mr-content');
  const status = root.querySelector('.mr-status');
  const nav = [...root.querySelectorAll('.site-nav a')];
  const features = {
    compras: {
      icon:'fa-truck-ramp-box',
      name:'Compras e fornecedores', short:'Registre a entrada de mercadorias por fornecedor.',
      lead:'Acompanhe uma compra do cadastro do fornecedor à conferência da ordem.',
      steps:[
        {
          id:'fornecedor', heading:'Gerencie seus fornecedores.',
          slides:[
            {
              label:'Lista',
              text:'Use a busca para encontrar um fornecedor. Na lista, confira os contatos, a situação do cadastro e os produtos vinculados.',
              capture:{
                file:'compras-fornecedores-lista', module:'Lista de fornecedores',
                alt:'Tela de Gestão de Fornecedores com indicadores, busca e lista de cinco fornecedores de demonstração',
                size:[1440, 960], caption:'Lista de fornecedores · Dados fictícios',
              },
            },
            {
              label:'Cadastro',
              text:'Cadastre um parceiro com os dados da empresa, o contato e o endereço de faturamento.',
              capture:{
                file:'compras-fornecedores-cadastro', module:'Cadastro de fornecedor',
                alt:'Formulário completo de cadastro de fornecedor com dados cadastrais, endereço de faturamento e ação de cadastrar',
                size:[1030, 797], caption:'Cadastro de fornecedor · Dados fictícios',
              },
            },
          ],
        },
        {
          id:'lancamento', heading:'Registre o que chegou.',
          text:'Vincule a nota ao fornecedor e informe a quantidade, o custo e o lote, quando aplicável. Na demonstração, a compra tem <strong>24 colas escolares</strong>.',
          capture:{
            file:'compras-lancamento', module:'Nova Compra',
            alt:'Formulário de compra da nota DEMO-001, com 24 colas escolares do lote COLA-DEMO-01 incluídas na lista',
            crop:[670, 416, 525, 145],
          },
        },
        {
          id:'ordem', heading:'Confira a ordem de compra.',
          text:'Depois do lançamento, consulte a nota, os itens e o status do pagamento. O total deste exemplo é de <strong>R$ 76,80</strong>.',
          capture:{
            file:'compras-ordem', module:'Ordem de Compra',
            alt:'Ordem de compra DEMO-001 com status de pagamento pendente e total de R$ 76,80',
            crop:[948, 171, 468, 183], desktopCrop:[270, 74, 1155, 582],
          },
        },
      ],
      related:['lotes','movimentos']
    },
    estoque: {
      icon:'fa-boxes-stacked',
      name:'Catálogo e estoque', short:'Encontre produtos, confira saldo e acompanhe a reposição.',
      lead:'Localize o produto por nome ou código e veja quando chegou a hora de repor.',
      steps:[
        {
          id:'catalogo', heading:'Encontre e cadastre produtos.',
          slides:[
            {
              label:'Catálogo',
              text:'Localize produtos pelo nome ou código de barras e consulte o saldo disponível no catálogo.',
              capture:{file:'ficha-estoque-catalogo', caption:'Catálogo · Dados fictícios', module:'Estoque e produtos', alt:'Catálogo de produtos com busca e saldos disponíveis', size:[1440, 850], crop:[282, 163, 845, 490]},
            },
            {
              label:'Cadastro',
              text:'Cadastre o produto com seu código, categoria e estoque mínimo. Esse limite serve de referência para os alertas de reposição.',
              capture:{file:'ficha-estoque-cadastro', caption:'Cadastro · Dados fictícios', module:'Cadastro de produto', alt:'Formulário de produto com identificação e estoque mínimo', size:[832, 633], crop:[16, 374, 800, 221]},
            },
          ],
        },
        {
          id:'reposicao', heading:'Saiba o que precisa repor.',
          text:'O painel reúne os produtos abaixo do estoque mínimo. No exemplo, o lápis grafite HB está com <strong>4 unidades</strong> e o mínimo é de <strong>5</strong>.',
          capture:{file:'painel', module:'Alerta de estoque', alt:'Alerta de lápis grafite HB, com saldo de 4 unidades e mínimo de 5', crop:[283, 514, 555, 185], desktopCrop:[283, 514, 555, 185]},
        },
      ],
      related:['compras','lotes']
    },
    lotes: {
      icon:'fa-calendar-days',
      name:'Lotes e validades', short:'Acompanhe quantidades e datas por lote.',
      lead:'Veja quais lotes vencem primeiro e quantas unidades ainda estão disponíveis.',
      steps:[
        {
          id:'validades', heading:'Veja o que vence primeiro.',
          slides:[
            {
              label:'Visão geral',
              text:'Confira a validade, o saldo de cada lote e os avisos de vencimento na mesma tela.',
              capture:{file:'ficha-lotes-lista', caption:'Lotes · Dados fictícios', module:'Lotes e Validades', alt:'Lista de lotes com data de validade, situação e saldo', size:[1440, 850], crop:[906, 444, 506, 252]},
            },
            {
              label:'Filtro',
              text:'Filtre os lotes que vencem em até 30 dias. Na demonstração, o lote de cola tem <strong>24 unidades</strong> e vence em <strong>18 dias</strong>.',
              capture:{file:'ficha-lotes-filtro', caption:'Filtro de validade · Dados fictícios', module:'Filtro de validade', alt:'Lista filtrada para lotes próximos do vencimento', size:[1155, 415], crop:[636, 19, 505, 321]},
            },
          ],
        },
        {
          id:'novo-lote', heading:'Registre a validade na entrada.',
          text:'Ao cadastrar um lote, selecione o produto e informe a quantidade e a data de validade. Assim, você consegue acompanhar cada lote separadamente.',
          capture:{file:'ficha-lotes-cadastro', module:'Cadastro de lote', alt:'Formulário de cadastro de lote com produto, quantidade e validade', size:[832, 510], crop:[0, 0, 832, 510], desktopCrop:[0, 0, 832, 510]},
        },
      ],
      related:['estoque','pdv']
    },
    pdv: {
      icon:'fa-cash-register',
      name:'Frente de caixa', short:'Localize produtos, confira o carrinho e registre a venda.',
      lead:'Da busca pelo código ao total da venda em uma mesma tela.',
      steps:[
        {
          id:'itens-da-venda', heading:'Monte a venda no balcão.',
          slides:[
            {
              label:'Busca',
              text:'Busque o produto pelo nome no catálogo do caixa ou use o leitor de código de barras para adicionar o item.',
              capture:{file:'ficha-pdv-busca', caption:'Busca no caixa · Dados fictícios', module:'Busca de produtos no caixa', alt:'Catálogo do caixa filtrado pela palavra Cola', crop:[762, 182, 622, 390]},
            },
            {
              label:'Carrinho',
              text:'Confira os itens, as quantidades e o subtotal antes de cobrar. Aqui, <strong>3 colas escolares</strong> somam <strong>R$ 17,70</strong>.',
              capture:{file:'ficha-pdv-carrinho', caption:'Carrinho · Dados fictícios', module:'Carrinho do caixa', alt:'Três colas escolares no carrinho e total de R$ 17,70', crop:[317, 183, 437, 460]},
            },
          ],
        },
        {
          id:'pagamento', heading:'Confira o pagamento.',
          text:'Escolha a forma de pagamento. Para uma venda de <strong>R$ 17,70</strong> com <strong>R$ 20,00</strong> em dinheiro, o caixa calcula <strong>R$ 2,30 de troco</strong>.',
          capture:{file:'ficha-pdv-pagamento', module:'Pagamento no caixa', alt:'Pagamento em dinheiro com valor recebido de R$ 20,00 e troco de R$ 2,30', size:[710, 726], crop:[109, 15, 495, 696], desktopCrop:[109, 15, 495, 696]},
        },
      ],
      related:['movimentos','lotes']
    },
    movimentos: {
      icon:'fa-arrow-right-arrow-left',
      name:'Movimentações', short:'Confira entradas, saídas e ajustes no histórico.',
      lead:'Veja de onde veio uma mudança no estoque.',
      steps:[
        {
          id:'historico', heading:'Entenda por que o saldo mudou.',
          slides:[
            {
              label:'Histórico',
              text:'O histórico reúne entradas e saídas de vários produtos. Consulte a data, o tipo e a origem de cada registro.',
              capture:{file:'ficha-movimentos-visao-geral', caption:'Histórico · Dados fictícios', module:'Histórico de estoque', alt:'Histórico de estoque com quatro movimentos de cola e papel A4', crop:[428, 487, 590, 335]},
            },
            {
              label:'Filtro',
              text:'Use os filtros de produto, tipo ou período. No exemplo da cola, entraram <strong>24 unidades</strong>, saíram <strong>3</strong> e restaram <strong>21</strong>.',
              capture:{file:'ficha-movimentos-historico', caption:'Filtro por produto · Dados fictícios', module:'Histórico filtrado por produto', alt:'Histórico da cola escolar com entrada de 24, saída de 3 e saldo de 21 unidades', crop:[945, 490, 469, 195]},
            },
          ],
        },
        {
          id:'ajuste', heading:'Registre o motivo de uma perda.',
          text:'Quando houver avaria, selecione o produto, informe a quantidade e registre o motivo. A justificativa ajuda a explicar a baixa na conferência do estoque.',
          capture:{file:'ficha-movimentos-registro', module:'Registro de perda ou avaria', alt:'Registro de perda de um lápis grafite HB com observação', size:[700, 590], crop:[101, 15, 498, 560], desktopCrop:[101, 15, 498, 560]},
        },
      ],
      related:['compras','pdv']
    },
    relatorios: {
      icon:'fa-chart-column',
      name:'Relatórios e análises', short:'Consulte relatórios e veja os produtos mais vendidos em quantidade.',
      pageTitle:'Relatórios e análises',
      lead:'Consulte o estoque e acompanhe as vendas por período.',
      steps:[
        {
          id:'exportacao', heading:'Exporte os relatórios de estoque.',
          text:'Na Central de Relatórios, escolha o que precisa conferir, como inventário ou estoque crítico. Use as opções de exportação em PDF ou Excel.',
          capture:{file:'ficha-relatorios-central', module:'Central de Relatórios', alt:'Central de Relatórios com opções de estoque e botões de exportação', size:[1440, 1097], crop:[272, 319, 578, 378], desktopCrop:[259, 63, 1181, 1034]},
        },
        {
          id:'analise', heading:'Acompanhe suas vendas.',
          slides:[
            {
              label:'Evolução',
              text:'Escolha um período e compare o faturamento, as vendas e o ticket médio.',
              capture:{file:'ficha-relatorios-vendas', caption:'Vendas · Dados fictícios', module:'Análise de vendas', alt:'Centro de Análise com indicadores e gráficos de vendas', size:[1440, 743], crop:[272, 321, 771, 422]},
            },
            {
              label:'Mais vendidos',
              text:'Veja os produtos mais vendidos em quantidade para entender quais itens tiveram mais saída no período.',
              capture:{file:'ficha-relatorios-ranking', caption:'Ranking · Dados fictícios', module:'Ranking de vendas', alt:'Ranking dos produtos mais vendidos e tabela de destaques', size:[1180, 445], crop:[0, 23, 590, 405]},
            },
          ],
        },
      ],
      related:['estoque','movimentos']
    }
  };
  const names = {funcionalidades:'Funcionalidades', solucoes:'Soluções',
    demonstracao:'Demonstração', contato:'Contato', acesso:'Acessar o sistema'};
  const publicPages = ['funcionalidades', 'solucoes', 'demonstracao', 'contato', 'acesso'];
  const closingTemplate = document.getElementById('encerramento-publico');
  const stages = [
    {
      id:'entrada', name:'Entrada', module:'Lotes e Validades', heading:'Receba o lote.',
      value:'24 unidades recebidas', text:'A compra registra a entrada de 24 colas escolares. A tela de Lotes mostra a validade e o saldo recebido.',
      file:'lotes', alt:'Lotes e Validades com o lote de cola escolar, validade e saldo de 24 unidades',
      focus:[914, 480, 414, 140], crop:[908, 475, 502, 152]
    },
    {
      id:'venda', name:'Venda', module:'Frente de Caixa', heading:'Confira a venda.',
      value:'3 colas no carrinho · R$ 17,70', text:'Na Frente de Caixa, confira a quantidade e o subtotal antes de confirmar a venda.',
      file:'pdv', alt:'Frente de Caixa com três colas escolares no carrinho e subtotal de R$ 17,70',
      focus:[321, 240, 429, 136], crop:[313, 182, 443, 200]
    },
    {
      id:'saldo', name:'Saldo', module:'Movimentações', heading:'Verifique o saldo.',
      value:'21 unidades em estoque', text:'O histórico reúne a entrada de 24 unidades e a saída de 3 na venda. A coluna de saldo atual mostra as 21 restantes.',
      file:'movimentacoes', alt:'Histórico com a entrada de 24 unidades, a saída de 3 e o saldo atual de 21 unidades',
      focus:[960, 487, 228, 196], crop:[947, 485, 259, 200]
    }
  ];

  const routeHref = id => './explorar.php?pagina=' + encodeURIComponent(id);

  const publicIntro = (id, heading, lead) => `
    <section class="catalog-hero" aria-labelledby="${id}">
      <h1 id="${id}">${heading}</h1><p>${lead}</p>
    </section>`;
  const catalogCard = (feature, heading = 'h2') => `
    <li data-home-reveal><a class="feature-card home-reveal-body${feature.contact ? ' contact-card' : ''}" href="${feature.href}"${feature.newTab ? ' target="_blank" rel="noopener noreferrer"' : ''}>
      <span class="feature-icon"><i class="${feature.iconSet || 'fa-solid'} ${feature.icon}" aria-hidden="true"></i></span>
      <${heading}>${feature.name}</${heading}>
      <p>${feature.short}</p>
      <span class="feature-link">${feature.label || 'Explorar recurso'} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></span>
    </a></li>`;
  const catalogGrid = (cards) => `
    <ul class="feature-grid catalog-grid" role="list">${cards.map(feature => catalogCard(feature)).join('')}</ul>`;

  function cropVariables([x, y, width, height], prefix, sourceWidth) {
    return `
      ${prefix}-ratio: ${width}/${height};
      ${prefix}-image-width: ${100 * sourceWidth / width}%;
      ${prefix}-image-left: ${-100 * x / width}%;
      ${prefix}-image-top: ${-100 * y / height}%;`;
  }

  function captureFigure({file, alt, crop, desktopCrop, module, size = [1440, 900], loading = 'lazy', caption = module + ' · Captura de demonstração'}, inCarousel = false) {
    const src = './landing/assets/landing/' + file + '.webp?v=r15';
    const fiscalNote = file === 'pdv' || file.startsWith('ficha-pdv-');
    const [width, height] = size;
    const cut = (crop ? cropVariables(crop, '--crop', width) : '')
      + (desktopCrop ? cropVariables(desktopCrop, '--desktop-crop', width) : '');
    const picture = `<img src="${src}" width="${width}" height="${height}" alt="${alt}" loading="${loading}" decoding="async">`;
    const carouselCrop = inCarousel && crop
      ? `<span class="detail-slide-crop" style="--carousel-crop-scale:${crop[2] / crop[3] * .8}">${picture}</span>`
      : picture;
    return `
      <figure class="demo-capture${fiscalNote ? ' has-fiscal-note' : ''}${desktopCrop && desktopCrop[2] < desktopCrop[3] ? ' detail-capture-portrait' : ''}">
        <a class="demo-capture-link" href="${src}" data-gallery aria-label="Ver captura completa de ${module}">
          <span class="demo-photo${desktopCrop ? ' detail-photo-crop' : ''}" style="${cut}">
            ${carouselCrop}
            <span class="zoom"><i class="fa-solid fa-up-right-and-down-left-from-center" aria-hidden="true"></i></span>
          </span>
          <span class="demo-gallery-link">Ver captura completa <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></span>
        </a>
        <figcaption>${caption}${fiscalNote ? ' · Emissão fiscal simulada, para fins acadêmicos' : ''}</figcaption>
      </figure>`;
  }

  function featureStep(step) {
    const slides = step.slides;
    const controlsId = 'telas-' + step.id;
    const copy = slides ? `
      <div class="detail-carousel-copy" aria-live="polite" aria-atomic="true">
        ${slides.map((slide, index) => `<p data-carousel-copy aria-hidden="${index !== 0}">${slide.text}</p>`).join('')}
      </div>
      <div class="detail-carousel-nav" role="group" aria-label="Escolher tela do carrossel">
        <button type="button" data-carousel-direction="-1" aria-controls="${controlsId}" aria-label="Tela anterior">
          <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
        </button>
        ${slides.map((slide, index) => `<button type="button" data-carousel-choice="${index}" aria-controls="${controlsId}" aria-pressed="${index === 0}">${slide.label}</button>`).join('')}
        <button type="button" data-carousel-direction="1" aria-controls="${controlsId}" aria-label="Próxima tela">
          <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
        </button>
      </div>` : `<p>${step.text}</p>`;
    const captures = slides ? `
      <div class="detail-carousel-views" id="${controlsId}">
        ${slides.map((slide, index) => `<div data-carousel-view${index === 0 ? '' : ' hidden'}>${captureFigure({...slide.capture, loading:'eager'}, true)}</div>`).join('')}
      </div>` : captureFigure(step.capture);
    return `
      <section class="detail-step" aria-labelledby="titulo-${step.id}" data-home-reveal${slides ? ' data-detail-carousel aria-roledescription="carrossel"' : ''}>
        <div class="demo-scene-body home-reveal-body">
          <div class="demo-copy">
            <h2 id="titulo-${step.id}">${step.heading}</h2>
            ${copy}
          </div>
          ${captures}
        </div>
      </section>`;
  }

  function bindDetailCarousel(carousel) {
    const nav = carousel.querySelector('.detail-carousel-nav');
    const media = carousel.querySelector('.detail-carousel-views');
    const views = [...media.querySelectorAll('[data-carousel-view]')];
    const descriptions = [...carousel.querySelectorAll('[data-carousel-copy]')];
    const choices = [...nav.querySelectorAll('[data-carousel-choice]')];
    let active = 0;
    let touchStart;

    function show(index) {
      active = (index + views.length) % views.length;
      views.forEach((view, i) => { view.hidden = i !== active; });
      descriptions.forEach((description, i) => description.setAttribute('aria-hidden', i !== active));
      choices.forEach((button, i) => button.setAttribute('aria-pressed', i === active));
    }

    nav.addEventListener('click', event => {
      const button = event.target.closest('button');
      if (!button) return;
      const {carouselChoice, carouselDirection} = button.dataset;
      show(carouselChoice === undefined ? active + Number(carouselDirection) : Number(carouselChoice));
    });
    nav.addEventListener('keydown', event => {
      if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
      if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
      event.preventDefault();
      show(active + (event.key === 'ArrowRight' ? 1 : -1));
    });
    media.addEventListener('touchstart', event => {
      touchStart = event.touches.length === 1 ? event.touches[0] : undefined;
    }, {passive:true});
    media.addEventListener('touchcancel', () => { touchStart = undefined; }, {passive:true});
    media.addEventListener('touchend', event => {
      const start = touchStart;
      touchStart = undefined;
      if (!start || event.touches.length) return;
      const end = event.changedTouches[0];
      const dx = end.clientX - start.clientX;
      const dy = end.clientY - start.clientY;
      if (Math.abs(dx) < 48 || Math.abs(dx) <= Math.abs(dy) * 1.3) return;
      if (event.cancelable) event.preventDefault();
      show(active + (dx < 0 ? 1 : -1));
    }, {passive:false});
  }

  function featurePage(id) {
    const feature = features[id];
    const related = feature.related.map(key => catalogCard({...features[key], href:routeHref(key)}, 'h3')).join('');
    return publicIntro('titulo-recurso', feature.pageTitle ?? feature.name, feature.lead) + `
      <div class="public-wrap detail-body">
        <nav aria-label="Voltar ao catálogo">
          <a class="detail-back" href="${routeHref('funcionalidades')}"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Todas as funcionalidades</a>
        </nav>
        <div class="detail-sequence">${feature.steps.map(featureStep).join('')}</div>
        <section class="detail-related" aria-labelledby="recursos-relacionados">
          <h2 id="recursos-relacionados">Recursos relacionados</h2>
          <ul class="feature-grid" role="list">${related}</ul>
        </section>
      </div>`;
  }

  function demoScene(step) {
    return `
      <article class="demo-scene" id="etapa-${step.id}" tabindex="-1" data-home-reveal>
        <div class="demo-scene-body home-reveal-body">
          <div class="demo-copy">
            <h2>${step.heading}</h2>
            <strong class="demo-value">${step.value}</strong>
            <p>${step.text}</p>
          </div>
          ${captureFigure(step)}
        </div>
      </article>`;
  }

  function demoFrame(step) {
    const [x, y, width, height] = step.focus;
    const position = `
      --focus-x: ${100 * x / 1440}%;
      --focus-y: ${100 * y / 900}%;
      --focus-width: ${100 * width / 1440}%;
      --focus-height: ${100 * height / 900}%;
      --focus-center-x: ${100 * (x + width / 2) / 1440}%;
      --focus-center-y: ${100 * (y + height / 2) / 900}%;`;
    return `
      <div class="demo-frame" data-module="${step.module}" data-value="${step.value}" style="${position}" hidden>
        <div class="demo-camera">
          <img width="1440" height="900" alt="" data-src="./landing/assets/landing/${step.file}.webp?v=r15">
          <span class="demo-spot"></span>
        </div>
      </div>`;
  }

  function demoPage() {
    return publicIntro('titulo-demonstracao', 'Da entrada ao saldo', 'Acompanhe a mesma cola escolar em três etapas, com dados fictícios.') + `
      <div class="public-wrap demo-journey">
        <nav class="demo-step-nav" aria-label="Etapas da demonstração">
          ${stages.map(step => `<a href="#etapa-${step.id}">${step.name}</a>`).join('')}
        </nav>
        <div class="demo-view">
          <div class="demo-window" aria-hidden="true" hidden>
            <div class="demo-window-bar"><strong data-demo-module></strong><span data-demo-value></span></div>
            <div class="demo-screen">${stages.map(demoFrame).join('')}</div>
          </div>
          <div class="demo-scenes">${stages.map(demoScene).join('')}</div>
        </div>
      </div>`;
  }

  function contactCards() {
    const [, phone, email] = closingTemplate.content.querySelectorAll('.footer-contact a');
    const whatsapp = closingTemplate.content.querySelector('.contact-float');
    return [
      {name:'WhatsApp', icon:'fa-whatsapp', iconSet:'fa-brands', short:'Peça uma apresentação ou converse sobre o projeto.', label:'Abrir WhatsApp', href:whatsapp.getAttribute('href'), contact:true, newTab:true},
      {name:'E-mail', icon:'fa-envelope', short:email.textContent.trim(), label:'Enviar e-mail', href:email.getAttribute('href'), contact:true},
      {name:'Telefone', icon:'fa-phone', short:phone.textContent.trim(), label:'Ligar', href:phone.getAttribute('href'), contact:true}
    ];
  }

  function accessPage() {
    return publicIntro('titulo-acesso', 'Acessar o sistema', 'Entre no MrStock ERP ou converse com a equipe sobre o projeto.') + `
      <div class="public-wrap access-options">
        <article class="access-card access-card-primary" aria-labelledby="titulo-entrar">
          <span class="feature-icon"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i></span>
          <h2 id="titulo-entrar">Já tenho acesso</h2>
          <p>Use as credenciais fornecidas pela equipe do MrStock.</p>
          <a class="button button-small" href="./login.php">
            Entrar no MrStock <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
          </a>
        </article>
        <article class="access-card" aria-labelledby="titulo-solicitar">
          <span class="feature-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></span>
          <h2 id="titulo-solicitar">Falar com a equipe do projeto</h2>
          <p>Veja os canais de contato para conhecer o projeto e esclarecer dúvidas sobre o acesso.</p>
          <a class="feature-link" href="${routeHref('contato')}">
            Falar com a equipe <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
          </a>
        </article>
      </div>`;
  }

  const pages = {
    funcionalidades: () => publicIntro('titulo-funcionalidades', 'Funcionalidades', 'Conheça as telas usadas para comprar, controlar o estoque e vender no balcão.')
      + catalogGrid(Object.entries(features).map(([id, feature]) => ({ ...feature, href:routeHref(id) }))),
    solucoes: () => publicIntro('titulo-solucoes', 'O que você quer resolver?', 'Escolha uma situação do dia a dia para ver onde o MrStock ajuda.')
      + catalogGrid([
        {name:'Repor antes de faltar', icon:'fa-boxes-stacked', short:'Veja os produtos que chegaram ao estoque mínimo.', label:'Ver estoque', href:routeHref('estoque')},
        {name:'Acompanhar vencimentos', icon:'fa-calendar-days', short:'Confira quais lotes exigem atenção, como colas e corretivos.', label:'Ver lotes', href:routeHref('lotes')},
        {name:'Conferir a venda e o saldo', icon:'fa-arrow-right-arrow-left', short:'Siga a mesma operação do carrinho até o histórico.', label:'Ver demonstração', href:routeHref('demonstracao')}
      ]),
    demonstracao: demoPage,
    contato: () => publicIntro('titulo-canais', 'Fale com a equipe', 'Conheça o projeto, peça uma apresentação ou tire dúvidas sobre o acesso ao ERP.')
      + catalogGrid(contactCards()),
    acesso: accessPage
  };

  function render(id) {
    root.dataset.page = id;
    const active = Object.hasOwn(features, id) ? 'funcionalidades' : id;
    nav.forEach(button => {
      if (button.dataset.go === active) button.setAttribute('aria-current','page');
      else button.removeAttribute('aria-current');
    });
    if (id === 'acesso') root.querySelector('.nav-access').setAttribute('aria-current', 'page');
    content.innerHTML = Object.hasOwn(features, id) ? featurePage(id) : pages[id]();
    const label = features[id]?.pageTitle ?? features[id]?.name ?? names[id];
    status.textContent = label;
    document.title = label + ' | MrStock ERP';
  }

  const requested = new URLSearchParams(location.search).get('pagina');
  const current = Object.hasOwn(pages, requested) || Object.hasOwn(features, requested)
    ? requested : 'funcionalidades';

  render(current);
  content.querySelectorAll('[data-detail-carousel]').forEach(bindDetailCarousel);
  if (content.querySelector('[data-gallery]')) {
    content.append(document.getElementById('galeria-publica').content.cloneNode(true));
  }
  if (publicPages.includes(current) || Object.hasOwn(features, current)) {
    root.dataset.publicPage = '';
    const closing = closingTemplate.content.cloneNode(true);
    const panel = closing.querySelector('.home-closing');
    if (current === 'contato') {
      panel.querySelector('h2').textContent = 'Já tem acesso ao MrStock?';
      panel.querySelector('p').textContent = 'Entre com as credenciais fornecidas pela equipe.';
      const link = panel.querySelector('.button');
      link.href = routeHref('acesso');
      link.firstChild.textContent = 'Acessar o sistema ';
    }
    if (current === 'acesso') panel.remove();
    else content.append(panel);
    root.after(closing.querySelector('.contact-float'));
    root.querySelector('.mr-footer').replaceWith(closing);
  }
})();
