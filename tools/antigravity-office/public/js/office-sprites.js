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
    // 1. Gera Pisos
    this.createFloorCarpet();
    this.createFloorWood();
    this.createFloorLab();
    this.createFloorBunker();

    // 2. Mobília
    this.createDesk();
    this.createDualMonitors();
    this.createServerRack();
    this.createOfficeChair();
    this.createCoffeeMachine();
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

  /* ================= PISOS (32x32) ================= */
  createFloorCarpet() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    ctx.fillStyle = '#1e3f2d';
    ctx.fillRect(0, 0, 32, 32);
    ctx.fillStyle = '#244b36';
    for (let x = 0; x < 32; x += 4) {
      for (let y = 0; y < 32; y += 4) {
        if ((x + y) % 8 === 0) ctx.fillRect(x, y, 2, 2);
      }
    }
    ctx.strokeStyle = '#162e21';
    ctx.strokeRect(0, 0, 32, 32);
    this.cache.set('floor_carpet', canvas);
  }

  createFloorWood() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    ctx.fillStyle = '#78350f';
    ctx.fillRect(0, 0, 32, 32);
    ctx.fillStyle = '#92400e';
    ctx.fillRect(0, 0, 32, 7);
    ctx.fillRect(0, 8, 32, 7);
    ctx.fillRect(0, 16, 32, 7);
    ctx.fillRect(0, 24, 32, 7);
    ctx.fillStyle = '#451a03';
    ctx.fillRect(0, 7, 32, 1);
    ctx.fillRect(0, 15, 32, 1);
    ctx.fillRect(0, 23, 32, 1);
    ctx.fillRect(0, 31, 32, 1);
    this.cache.set('floor_wood', canvas);
  }

  createFloorLab() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    ctx.fillStyle = '#e2e8f0';
    ctx.fillRect(0, 0, 32, 32);
    ctx.fillStyle = '#cbd5e1';
    ctx.fillRect(1, 1, 30, 30);
    ctx.fillStyle = '#f8fafc';
    ctx.fillRect(2, 2, 28, 28);
    ctx.fillStyle = '#94a3b8';
    ctx.strokeRect(0, 0, 32, 32);
    this.cache.set('floor_lab', canvas);
  }

  createFloorBunker() {
    const { canvas, ctx } = this.createCanvas(32, 32);
    ctx.fillStyle = '#1e293b';
    ctx.fillRect(0, 0, 32, 32);
    ctx.fillStyle = '#334155';
    ctx.fillRect(2, 2, 28, 28);
    // Rebites nos 4 cantos
    ctx.fillStyle = '#0f172a';
    ctx.fillRect(4, 4, 2, 2);
    ctx.fillRect(26, 4, 2, 2);
    ctx.fillRect(4, 26, 2, 2);
    ctx.fillRect(26, 26, 2, 2);
    this.cache.set('floor_bunker', canvas);
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
    const { canvas, ctx } = this.createCanvas(20, 24);
    ctx.fillStyle = '#334155';
    ctx.fillRect(2, 4, 16, 18);
    // Jarra de vidro com café
    ctx.fillStyle = '#e2e8f0';
    ctx.fillRect(4, 10, 12, 10);
    ctx.fillStyle = '#451a03';
    ctx.fillRect(5, 12, 10, 7);
    this.cache.set('coffee_machine', canvas);
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
