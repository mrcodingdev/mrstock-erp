/**
 * ANTIGRAVITY OFFICE — PROCEDURAL SPRITE FACTORY
 * Gera todas as texturas, mobílias e avatares via Offscreen Canvas
 * Zero dependências de arquivos de imagem externos!
 */

class OfficeSprites {
  constructor() {
    this.cache = new Map();
    this.init();
  }

  init() {
    // 1. Gera Pisos Harmoniosos e Carpetes Setoriais
    this.createFloorWood();
    this.createCarpetGovernance();
    this.createCarpetOrchestration();
    this.createFloorBunker();
    this.createFloorCaution();
    this.createCarpetFrontline();
    this.createFloorQA();
    this.createCarpetLounge();
    this.createWallPartition();

    // 2. Mobília
    this.createDesk();
    this.createDualMonitors();
    this.createServerRack();
    this.createOfficeChair();
    this.createCoffeeMachine();
    this.createWaterCooler();
    this.createPottedPlant();
    this.createBookshelf();

    // 3. Ícones de Ferramentas
    this.createToolIcons();

    // 4. Avatares
    this.generateAllAvatars();
  }

  createCanvas(w, h) {
    const c = document.createElement('canvas');
    c.width = w;
    c.height = h;
    const ctx = c.getContext('2d');
    ctx.imageSmoothingEnabled = false;
    return { canvas: c, ctx };
  }

  /* ================= PISOS & DIVISÓRIAS (32x32) ================= */
  createFloorWood() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Base de carvalho nobre executivo acolhedor e sóbrio (#43302b / #543d37)
    ctx.fillStyle = '#43302b';
    ctx.fillRect(0, 0, 32, 32);

    // Pranchas de madeira em faixas
    ctx.fillStyle = '#543d37';
    ctx.fillRect(0, 1, 32, 7);
    ctx.fillRect(0, 9, 32, 7);
    ctx.fillRect(0, 17, 32, 7);
    ctx.fillRect(0, 25, 32, 7);

    // Linhas de chanfro escuro entre pranchas
    ctx.fillStyle = '#2d1f1c';
    ctx.fillRect(0, 0, 32, 1);
    ctx.fillRect(0, 8, 32, 1);
    ctx.fillRect(0, 16, 32, 1);
    ctx.fillRect(0, 24, 32, 1);

    // Emendas verticais desencontradas
    ctx.fillRect(14, 1, 1, 7);
    ctx.fillRect(28, 9, 1, 7);
    ctx.fillRect(6, 17, 1, 7);
    ctx.fillRect(22, 25, 1, 7);

    // Textura sutil de veios da madeira
    ctx.fillStyle = '#3c2925';
    ctx.fillRect(4, 3, 3, 1);
    ctx.fillRect(20, 11, 4, 1);
    ctx.fillRect(10, 19, 2, 1);
    ctx.fillRect(15, 27, 3, 1);

    this.cache.set('floor_wood', canvas);
  }

  createCarpetGovernance() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Carpete Verde Nobre Papelaria Real (#152e22 com borda #284936)
    ctx.fillStyle = '#152e22';
    ctx.fillRect(0, 0, 32, 32);

    ctx.fillStyle = '#1b3a2b';
    for (let x = 2; x < 32; x += 4) {
      for (let y = 2; y < 32; y += 4) {
        if ((x + y) % 8 === 0) ctx.fillRect(x, y, 2, 2);
      }
    }
    ctx.strokeStyle = '#284936';
    ctx.lineWidth = 1;
    ctx.strokeRect(0.5, 0.5, 31, 31);
    this.cache.set('carpet_governance', canvas);
  }

  createCarpetOrchestration() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Carpete Real Violeta Tech (#221533 com borda #581c87)
    ctx.fillStyle = '#221533';
    ctx.fillRect(0, 0, 32, 32);

    ctx.fillStyle = '#2c1b42';
    for (let x = 2; x < 32; x += 4) {
      for (let y = 2; y < 32; y += 4) {
        if ((x + y) % 8 === 0) ctx.fillRect(x, y, 2, 2);
      }
    }
    ctx.strokeStyle = '#581c87';
    ctx.lineWidth = 1;
    ctx.strokeRect(0.5, 0.5, 31, 31);
    this.cache.set('carpet_orchestration', canvas);
  }

  createFloorBunker() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Aço escovado Cyber com rebites (#141a24)
    ctx.fillStyle = '#141a24';
    ctx.fillRect(0, 0, 32, 32);

    ctx.fillStyle = '#1c2432';
    ctx.fillRect(2, 2, 28, 28);

    ctx.fillStyle = '#222d3d';
    ctx.fillRect(4, 8, 24, 1);
    ctx.fillRect(4, 16, 24, 1);
    ctx.fillRect(4, 24, 24, 1);

    // Rebites nos 4 cantos
    ctx.fillStyle = '#38495f';
    ctx.fillRect(4, 4, 2, 2);
    ctx.fillRect(26, 4, 2, 2);
    ctx.fillRect(4, 26, 2, 2);
    ctx.fillRect(26, 26, 2, 2);
    ctx.fillStyle = '#080b0f';
    ctx.fillRect(5, 5, 1, 1);
    ctx.fillRect(27, 5, 1, 1);
    ctx.fillRect(5, 27, 1, 1);
    ctx.fillRect(27, 27, 1, 1);

    this.cache.set('floor_bunker', canvas);
  }

  createFloorCaution() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Faixa diagonal amarela/preta de advertência para a entrada do Bunker
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(0, 0, 32, 32);

    ctx.fillStyle = '#eab308';
    for (let i = -32; i < 64; i += 12) {
      ctx.beginPath();
      ctx.moveTo(i, 0);
      ctx.lineTo(i + 6, 0);
      ctx.lineTo(i + 6 + 32, 32);
      ctx.lineTo(i + 32, 32);
      ctx.closePath();
      ctx.fill();
    }
    ctx.strokeStyle = '#334155';
    ctx.lineWidth = 1;
    ctx.strokeRect(0.5, 0.5, 31, 31);
    this.cache.set('floor_caution', canvas);
  }

  createCarpetFrontline() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Carpete Azul Marinho Tech (#122338 com borda #1e3a5f)
    ctx.fillStyle = '#122338';
    ctx.fillRect(0, 0, 32, 32);

    ctx.fillStyle = '#172c46';
    for (let x = 2; x < 32; x += 4) {
      for (let y = 2; y < 32; y += 4) {
        if ((x + y) % 8 === 0) ctx.fillRect(x, y, 2, 2);
      }
    }
    ctx.strokeStyle = '#1e3a5f';
    ctx.lineWidth = 1;
    ctx.strokeRect(0.5, 0.5, 31, 31);
    this.cache.set('carpet_frontline', canvas);
  }

  createFloorQA() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Piso Técnico Ardósia/Ciano suave (#1f2d36 com detalhe #0e7490, sem branco ofuscante)
    ctx.fillStyle = '#1f2d36';
    ctx.fillRect(0, 0, 32, 32);

    ctx.fillStyle = '#263742';
    ctx.fillRect(2, 2, 28, 28);

    ctx.fillStyle = '#0e7490';
    ctx.fillRect(2, 2, 4, 1);
    ctx.fillRect(2, 2, 1, 4);
    ctx.fillRect(26, 2, 4, 1);
    ctx.fillRect(29, 2, 1, 4);
    ctx.fillRect(2, 29, 4, 1);
    ctx.fillRect(2, 26, 1, 4);
    ctx.fillRect(26, 29, 4, 1);
    ctx.fillRect(29, 26, 1, 4);
    ctx.fillRect(15, 15, 2, 2);

    this.cache.set('floor_qa', canvas);
  }

  createCarpetLounge() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Tapete Mocha para o lounge central
    ctx.fillStyle = '#2e221c';
    ctx.fillRect(0, 0, 32, 32);

    ctx.fillStyle = '#3a2b24';
    for (let x = 2; x < 32; x += 4) {
      for (let y = 2; y < 32; y += 4) {
        if ((x + y) % 8 === 0) ctx.fillRect(x, y, 2, 2);
      }
    }
    ctx.strokeStyle = '#523c32';
    ctx.lineWidth = 1;
    ctx.strokeRect(0.5, 0.5, 31, 31);
    this.cache.set('carpet_lounge', canvas);
  }

  createWallPartition() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    // Divisória de vidro fumê com moldura metálica escura
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(0, 26, 32, 6);
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(0, 26, 32, 2);

    // Vidro fumê translúcido
    ctx.fillStyle = 'rgba(23, 37, 54, 0.88)';
    ctx.fillRect(2, 4, 28, 22);

    // Montantes verticais
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(0, 4, 2, 22);
    ctx.fillRect(30, 4, 2, 22);

    // Reflexo especular
    ctx.fillStyle = 'rgba(186, 230, 253, 0.18)';
    ctx.beginPath();
    ctx.moveTo(6, 24);
    ctx.lineTo(14, 6);
    ctx.lineTo(17, 6);
    ctx.lineTo(9, 24);
    ctx.closePath();
    ctx.fill();

    // Trilho superior metálico
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(0, 0, 32, 4);
    ctx.fillStyle = '#475569';
    ctx.fillRect(0, 0, 32, 1);

    this.cache.set('wall_partition', canvas);
  }

  /* ================= MOBÍLIA ================= */
  createDesk() {
    const { canvas, ctx } = this.createCanvas(64, 36);
    // Tampo de madeira mogno
    ctx.fillStyle = '#7c2d12';
    ctx.fillRect(0, 4, 64, 28);
    ctx.fillStyle = '#9a3412';
    ctx.fillRect(2, 6, 60, 24);
    // Chanfro e gaveteiro
    ctx.fillStyle = '#431407';
    ctx.fillRect(0, 32, 64, 4);
    ctx.fillRect(48, 10, 12, 18);
    ctx.fillStyle = '#fbbf24';
    ctx.fillRect(53, 14, 2, 2);
    ctx.fillRect(53, 20, 2, 2);
    this.cache.set('desk', canvas);
  }

  createDualMonitors() {
    const { canvas, ctx } = this.createCanvas(48, 22);
    // Monitor 1 (Esquerda)
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(2, 0, 20, 16);
    ctx.fillStyle = '#022c22';
    ctx.fillRect(4, 2, 16, 12);
    ctx.fillStyle = '#6ae49b';
    ctx.fillRect(6, 4, 12, 1);
    ctx.fillRect(6, 7, 8, 1);
    ctx.fillRect(6, 10, 10, 1);
    ctx.fillStyle = '#334155';
    ctx.fillRect(10, 16, 4, 5);

    // Monitor 2 (Direita)
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(26, 0, 20, 16);
    ctx.fillStyle = '#0c4a6e';
    ctx.fillRect(28, 2, 16, 12);
    ctx.fillStyle = '#38bdf8';
    ctx.fillRect(30, 4, 12, 1);
    ctx.fillRect(30, 7, 10, 1);
    ctx.fillRect(30, 10, 6, 1);
    ctx.fillStyle = '#334155';
    ctx.fillRect(34, 16, 4, 5);
    this.cache.set('dual_monitors', canvas);
  }

  createServerRack() {
    const { canvas, ctx } = this.createCanvas(28, 54);
    ctx.fillStyle = '#020617';
    ctx.fillRect(0, 0, 28, 54);
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(2, 2, 24, 50);

    // Unidades de Rack com LEDs
    for (let y = 6; y < 48; y += 8) {
      ctx.fillStyle = '#0f172a';
      ctx.fillRect(4, y, 20, 6);
      // LEDs coloridos
      ctx.fillStyle = '#22c55e';
      ctx.fillRect(6, y + 2, 2, 2);
      ctx.fillStyle = '#3b82f6';
      ctx.fillRect(10, y + 2, 2, 2);
      ctx.fillStyle = '#ef4444';
      ctx.fillRect(14, y + 2, 2, 2);
    }
    this.cache.set('server_rack', canvas);
  }

  createOfficeChair() {
    const { canvas, ctx } = this.createCanvas(24, 24);
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(4, 2, 16, 14);
    ctx.fillStyle = '#334155';
    ctx.fillRect(6, 4, 12, 10);
    // Base e rodízios
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(11, 16, 2, 4);
    ctx.fillRect(7, 20, 10, 2);
    this.cache.set('chair', canvas);
  }

  createCoffeeMachine() {
    const { canvas, ctx } = this.createCanvas(24, 28);
    // Balcão de apoio
    ctx.fillStyle = '#451a03';
    ctx.fillRect(0, 14, 24, 14);
    ctx.fillStyle = '#78350f';
    ctx.fillRect(1, 15, 22, 12);
    // Cafeteira expresso inox
    ctx.fillStyle = '#475569';
    ctx.fillRect(3, 2, 18, 14);
    ctx.fillStyle = '#94a3b8';
    ctx.fillRect(5, 4, 14, 10);
    // LED e Jarra
    ctx.fillStyle = '#22c55e';
    ctx.fillRect(6, 5, 2, 2);
    ctx.fillStyle = '#38bdf8';
    ctx.fillRect(7, 8, 10, 6);
    ctx.fillStyle = '#3b1c09';
    ctx.fillRect(8, 9, 8, 4);
    this.cache.set('coffee_machine', canvas);
  }

  createWaterCooler() {
    const { canvas, ctx } = this.createCanvas(24, 34);
    // Garrafão de água azul translúcido
    ctx.fillStyle = '#0284c7';
    ctx.fillRect(4, 2, 16, 12);
    ctx.fillStyle = '#38bdf8';
    ctx.fillRect(6, 3, 4, 10);
    ctx.fillStyle = '#0369a1';
    ctx.fillRect(8, 14, 8, 2);

    // Gabinete vertical
    ctx.fillStyle = '#cbd5e1';
    ctx.fillRect(3, 16, 18, 18);
    ctx.fillStyle = '#f8fafc';
    ctx.fillRect(5, 17, 14, 16);

    // Nicho das torneiras
    ctx.fillStyle = '#334155';
    ctx.fillRect(7, 19, 10, 6);
    ctx.fillStyle = '#38bdf8'; // fria
    ctx.fillRect(8, 20, 2, 3);
    ctx.fillStyle = '#ef4444'; // quente
    ctx.fillRect(14, 20, 2, 3);
    ctx.fillStyle = '#64748b'; // pingadeira
    ctx.fillRect(7, 24, 10, 1);

    this.cache.set('water_cooler', canvas);
  }

  createPottedPlant() {
    const { canvas, ctx } = this.createCanvas(24, 32);
    // Folhagem verde Papelaria Real
    ctx.fillStyle = '#15803d';
    ctx.fillRect(4, 2, 16, 14);
    ctx.fillStyle = '#22c55e';
    ctx.fillRect(6, 4, 12, 10);
    // Vaso de cerâmica terracota
    ctx.fillStyle = '#ea580c';
    ctx.fillRect(6, 16, 12, 14);
    ctx.fillStyle = '#c2410c';
    ctx.fillRect(7, 18, 10, 10);
    this.cache.set('plant', canvas);
  }

  createBookshelf() {
    const { canvas, ctx } = this.createCanvas(48, 54);
    ctx.fillStyle = '#78350f';
    ctx.fillRect(0, 0, 48, 54);
    // Prateleiras com livros coloridos
    for (let y = 6; y < 48; y += 14) {
      ctx.fillStyle = '#451a03';
      ctx.fillRect(4, y + 10, 40, 3);
      // Livros
      ctx.fillStyle = '#ef4444'; ctx.fillRect(6, y, 4, 10);
      ctx.fillStyle = '#3b82f6'; ctx.fillRect(11, y, 5, 10);
      ctx.fillStyle = '#10b981'; ctx.fillRect(17, y, 4, 10);
      ctx.fillStyle = '#f59e0b'; ctx.fillRect(22, y, 6, 10);
      ctx.fillStyle = '#8b5cf6'; ctx.fillRect(29, y, 4, 10);
    }
    this.cache.set('bookshelf', canvas);
  }

  createToolIcons() {
    const tools = ['terminal', 'code', 'file', 'search', 'shield', 'audit'];
    tools.forEach(tool => {
      const { canvas, ctx } = this.createCanvas(14, 14);
      ctx.fillStyle = '#0f172a';
      ctx.fillRect(0, 0, 14, 14);
      ctx.strokeStyle = '#6ae49b';
      ctx.strokeRect(0, 0, 14, 14);

      ctx.fillStyle = '#6ae49b';
      if (tool === 'terminal') {
        ctx.fillRect(2, 3, 2, 2);
        ctx.fillRect(4, 5, 2, 2);
        ctx.fillRect(2, 7, 2, 2);
        ctx.fillRect(6, 9, 5, 1);
      } else if (tool === 'code') {
        ctx.fillRect(3, 4, 2, 4);
        ctx.fillRect(9, 4, 2, 4);
        ctx.fillRect(6, 3, 2, 6);
      } else if (tool === 'file') {
        ctx.fillRect(3, 2, 8, 10);
        ctx.fillStyle = '#0f172a';
        ctx.fillRect(4, 4, 6, 1);
        ctx.fillRect(4, 6, 6, 1);
        ctx.fillRect(4, 8, 4, 1);
      } else {
        ctx.fillRect(4, 4, 6, 6);
      }
      this.cache.set(`tool_${tool}`, canvas);
    });
  }

  /* ================= AVATARES (32x32) ================= */
  generateAllAvatars() {
    const avatarStyles = [
      { id: 'antigravity-orchestrator', robe: '#9333ea', hair: '#f8fafc', skin: '#fcd34d', crown: true },
      { id: 'chief-erp-architect', robe: '#1a4231', hair: '#64748b', skin: '#fde047', glasses: true },
      { id: 'code-reviewer', robe: '#0284c7', hair: '#334155', skin: '#fed7aa', magnifier: true },
      { id: 'security-auditor', robe: '#18181b', hair: '#ef4444', skin: '#fde047', hood: true },
      { id: 'software-engineer', robe: '#284936', hair: '#1e293b', skin: '#fed7aa', headset: true },
      { id: 'backend-engineer', robe: '#b45309', hair: '#451a03', skin: '#fcd34d', beard: true },
      { id: 'frontend-engineer', robe: '#db2777', hair: '#6366f1', skin: '#fed7aa', shades: true },
      { id: 'test-engineer', robe: '#65a30d', hair: '#0f172a', skin: '#fde047', helmet: true },
      { id: 'web-performance-auditor', robe: '#ca8a04', hair: '#172554', skin: '#fed7aa', stopwatch: true },
      { id: 'anti-slop-ui-auditor', robe: '#0d9488', hair: '#831843', skin: '#fde047', beret: true }
    ];

    avatarStyles.forEach(style => {
      ['idle_0', 'idle_1', 'typing_0', 'typing_1', 'pass', 'revise'].forEach(anim => {
        const { canvas, ctx } = this.createCanvas(32, 32);
        this.renderCharacterFrame(ctx, style, anim);
        this.cache.set(`avatar_${style.id}_${anim}`, canvas);
      });
    });
  }

  renderCharacterFrame(ctx, style, anim) {
    const isTyping = anim.startsWith('typing');
    const isPass = anim === 'pass';
    const isRevise = anim === 'revise';
    const bob = anim === 'idle_1' ? 1 : 0;

    // Sombra
    ctx.fillStyle = 'rgba(0, 0, 0, 0.3)';
    ctx.beginPath();
    ctx.ellipse(16, 29, 9, 3, 0, 0, Math.PI * 2);
    ctx.fill();

    // Tronco / Roupa
    ctx.fillStyle = style.robe;
    ctx.fillRect(10, 16 + bob, 12, 11);

    // Cabeça / Rosto
    ctx.fillStyle = style.skin;
    ctx.fillRect(11, 7 + bob, 10, 9);

    // Olhos
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(13, 10 + bob, 2, 2);
    ctx.fillRect(17, 10 + bob, 2, 2);

    // Cabelo / Acessório de Cabeça
    if (style.hood) {
      ctx.fillStyle = '#09090b';
      ctx.fillRect(9, 5 + bob, 14, 4);
      ctx.fillRect(9, 5 + bob, 2, 11);
      ctx.fillRect(21, 5 + bob, 2, 11);
    } else if (style.helmet) {
      ctx.fillStyle = '#eab308';
      ctx.fillRect(9, 4 + bob, 14, 5);
      ctx.fillStyle = '#fff';
      ctx.fillRect(15, 6 + bob, 2, 2);
    } else if (style.beret) {
      ctx.fillStyle = '#831843';
      ctx.fillRect(8, 4 + bob, 16, 4);
    } else {
      ctx.fillStyle = style.hair;
      ctx.fillRect(10, 5 + bob, 12, 4);
    }

    // Óculos
    if (style.glasses || style.shades) {
      ctx.fillStyle = style.shades ? '#0f172a' : '#38bdf8';
      ctx.fillRect(12, 9 + bob, 3, 3);
      ctx.fillRect(17, 9 + bob, 3, 3);
      ctx.fillRect(15, 10 + bob, 2, 1);
    }

    // Braços conforme Animação
    ctx.fillStyle = style.robe;
    if (isPass) {
      // Braços erguidos em comemoração
      ctx.fillRect(6, 8, 4, 10);
      ctx.fillRect(22, 8, 4, 10);
    } else if (isTyping) {
      // Braços no teclado alternando
      const toggle = anim === 'typing_0';
      ctx.fillRect(7, 17, 4, 8);
      ctx.fillRect(21, 17, 4, 8);
      ctx.fillStyle = style.skin;
      ctx.fillRect(9, toggle ? 23 : 21, 3, 3);
      ctx.fillRect(20, toggle ? 21 : 23, 3, 3);
    } else if (isRevise) {
      // Mão no queixo
      ctx.fillRect(7, 18, 4, 8);
      ctx.fillRect(21, 18, 4, 8);
      ctx.fillStyle = style.skin;
      ctx.fillRect(15, 14, 3, 3);
    } else {
      // Idle
      ctx.fillRect(7, 18 + bob, 3, 8);
      ctx.fillRect(22, 18 + bob, 3, 8);
    }
  }

  get(key) {
    return this.cache.get(key) || null;
  }
}

window.officeSprites = new OfficeSprites();
