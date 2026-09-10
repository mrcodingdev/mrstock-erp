/**
 * ANTIGRAVITY OFFICE — 2D PIXEL ART GRAPHICS ENGINE
 * Motor Canvas 60 FPS com 5 Salas Temáticas, Partículas e Handoffs Voadores
 */

class OfficeEngine {
  constructor(canvasId) {
    this.canvas = document.getElementById(canvasId);
    this.ctx = this.canvas.getContext('2d');
    this.sprites = window.officeSprites;

    this.mapCols = 46;
    this.mapRows = 26;
    this.tileSize = 32;

    // Câmera & Interatividade
    this.camera = { x: 736, y: 416, zoom: 1.0, targetX: 736, targetY: 416, targetZoom: 1.0 };
    this.isDragging = false;
    this.dragStart = { x: 0, y: 0 };
    this.mouseWorld = { x: 0, y: 0 };
    this.hoveredAgent = null;

    // Sistema de Partículas e Handoffs Voadores
    this.particles = [];
    this.flyingHandoffs = [];

    // Agentes sincronizados
    this.agents = [];
    this.hasInitialFitted = false;

    this.setupListeners();
    this.setupTouchListeners();
    this.resize();
    window.addEventListener('resize', () => this.resize());
  }

  setupListeners() {
    this.canvas.addEventListener('mousedown', (e) => {
      this.isDragging = true;
      this.dragStart = { x: e.clientX, y: e.clientY };
    });

    window.addEventListener('mousemove', (e) => {
      const rect = this.canvas.getBoundingClientRect();
      const sx = e.clientX - rect.left;
      const sy = e.clientY - rect.top;

      this.mouseWorld = this.screenToWorld(sx, sy);

      if (this.isDragging) {
        const dx = (e.clientX - this.dragStart.x) / this.camera.zoom;
        const dy = (e.clientY - this.dragStart.y) / this.camera.zoom;
        this.camera.x -= dx;
        this.camera.y -= dy;
        this.camera.targetX = this.camera.x;
        this.camera.targetY = this.camera.y;
        this.dragStart = { x: e.clientX, y: e.clientY };
      }

      this.checkHover();
    });

    window.addEventListener('mouseup', () => { this.isDragging = false; });

    this.canvas.addEventListener('wheel', (e) => {
      e.preventDefault();
      const zoomFactor = e.deltaY < 0 ? 1.15 : 0.85;
      this.camera.targetZoom = Math.max(0.5, Math.min(2.5, this.camera.targetZoom * zoomFactor));
    }, { passive: false });

    this.canvas.addEventListener('click', () => {
      if (this.hoveredAgent && window.officeUI) {
        window.officeUI.openAgentCockpit(this.hoveredAgent);
      }
    });
  }

  setupTouchListeners() {
    let touchStartDist = 0;
    let initialTouchZoom = 1.0;
    let lastTouchX = 0;
    let lastTouchY = 0;
    let isTouching = false;
    let touchStartTime = 0;

    this.canvas.addEventListener('touchstart', (e) => {
      touchStartTime = Date.now();
      if (e.touches.length === 1) {
        isTouching = true;
        lastTouchX = e.touches[0].clientX;
        lastTouchY = e.touches[0].clientY;
        const rect = this.canvas.getBoundingClientRect();
        this.mouseWorld = this.screenToWorld(lastTouchX - rect.left, lastTouchY - rect.top);
        this.checkHover();
      } else if (e.touches.length === 2) {
        isTouching = false;
        const dx = e.touches[0].clientX - e.touches[1].clientX;
        const dy = e.touches[0].clientY - e.touches[1].clientY;
        touchStartDist = Math.hypot(dx, dy);
        initialTouchZoom = this.camera.targetZoom;
      }
    }, { passive: false });

    window.addEventListener('touchmove', (e) => {
      if (e.touches.length === 1 && isTouching) {
        e.preventDefault();
        const curX = e.touches[0].clientX;
        const curY = e.touches[0].clientY;
        const dx = (curX - lastTouchX) / this.camera.zoom;
        const dy = (curY - lastTouchY) / this.camera.zoom;
        this.camera.x -= dx;
        this.camera.y -= dy;
        this.camera.targetX = this.camera.x;
        this.camera.targetY = this.camera.y;
        lastTouchX = curX;
        lastTouchY = curY;
        const rect = this.canvas.getBoundingClientRect();
        this.mouseWorld = this.screenToWorld(curX - rect.left, curY - rect.top);
        this.checkHover();
      } else if (e.touches.length === 2 && touchStartDist > 0) {
        e.preventDefault();
        const dx = e.touches[0].clientX - e.touches[1].clientX;
        const dy = e.touches[0].clientY - e.touches[1].clientY;
        const currentDist = Math.hypot(dx, dy);
        const scale = currentDist / touchStartDist;
        this.camera.targetZoom = Math.max(0.35, Math.min(2.5, initialTouchZoom * scale));
      }
    }, { passive: false });

    window.addEventListener('touchend', (e) => {
      if (e.touches.length < 2) {
        touchStartDist = 0;
      }
      if (e.touches.length === 0) {
        isTouching = false;
        if (Date.now() - touchStartTime < 300 && this.hoveredAgent && window.officeUI) {
          window.officeUI.openAgentCockpit(this.hoveredAgent);
        }
      }
    });
  }

  resize() {
    this.canvas.width = this.canvas.parentElement.clientWidth;
    this.canvas.height = this.canvas.parentElement.clientHeight;
    this.ctx.imageSmoothingEnabled = false;

    const worldCenterX = (this.mapCols * this.tileSize) / 2;
    const worldCenterY = (this.mapRows * this.tileSize) / 2;
    const distToCenter = Math.hypot(this.camera.x - worldCenterX, this.camera.y - worldCenterY);

    if (!this.hasInitialFitted || distToCenter < 100) {
      this.fitToScreen();
      this.hasInitialFitted = true;
    }
  }

  fitToScreen(padding = 24) {
    const worldW = this.mapCols * this.tileSize;
    const worldH = this.mapRows * this.tileSize;
    const availW = Math.max(200, this.canvas.width - padding * 2);
    const availH = Math.max(200, this.canvas.height - padding * 2);
    const scaleX = availW / worldW;
    const scaleY = availH / worldH;
    const bestZoom = Math.max(0.35, Math.min(2.0, Math.min(scaleX, scaleY)));

    this.camera.targetX = worldW / 2;
    this.camera.targetY = worldH / 2;
    this.camera.targetZoom = bestZoom;
  }

  panToSector(sectorId) {
    const sectors = {
      governance: { x: 9 * 32, y: 6 * 32, zoom: 1.35 },
      orchestration: { x: 23 * 32, y: 6 * 32, zoom: 1.35 },
      bunker: { x: 36 * 32, y: 6 * 32, zoom: 1.35 },
      development: { x: 12 * 32, y: 19 * 32, zoom: 1.35 },
      qa_lab: { x: 34 * 32, y: 19 * 32, zoom: 1.35 }
    };

    const target = sectors[sectorId];
    if (target) {
      this.camera.targetX = target.x;
      this.camera.targetY = target.y;
      this.camera.targetZoom = target.zoom;
    }
  }

  screenToWorld(sx, sy) {
    return {
      x: (sx - this.canvas.width / 2) / this.camera.zoom + this.camera.x,
      y: (sy - this.canvas.height / 2) / this.camera.zoom + this.camera.y
    };
  }

  worldToScreen(wx, wy) {
    return {
      x: (wx - this.camera.x) * this.camera.zoom + this.canvas.width / 2,
      y: (wy - this.camera.y) * this.camera.zoom + this.canvas.height / 2
    };
  }

  updateAgents(agents) {
    this.agents = agents;
  }

  triggerHandoff(handoff) {
    const fromDesk = handoff.fromDesk || { x: 22, y: 5 };
    const toDesk = handoff.toDesk || { x: 6, y: 18 };

    this.flyingHandoffs.push({
      x: fromDesk.x * this.tileSize + 16,
      y: fromDesk.y * this.tileSize + 16,
      startX: fromDesk.x * this.tileSize + 16,
      startY: fromDesk.y * this.tileSize + 16,
      targetX: toDesk.x * this.tileSize + 16,
      targetY: toDesk.y * this.tileSize + 16,
      progress: 0,
      speed: 0.015,
      trail: []
    });

    if (window.audioFx) window.audioFx.playHandoffWhoosh();
  }

  spawnConfetti(wx, wy) {
    const colors = ['#facc15', '#4ade80', '#38bdf8', '#ec4899', '#a855f7'];
    for (let i = 0; i < 30; i++) {
      this.particles.push({
        x: wx,
        y: wy,
        vx: (Math.random() - 0.5) * 6,
        vy: -Math.random() * 6 - 2,
        size: Math.random() * 4 + 2,
        color: colors[Math.floor(Math.random() * colors.length)],
        life: 1.0,
        decay: Math.random() * 0.02 + 0.01
      });
    }
  }

  checkHover() {
    this.hoveredAgent = null;
    const tooltip = document.getElementById('agent-hover-tooltip');

    for (const agent of this.agents) {
      const ax = agent.deskCoord.x * this.tileSize;
      const ay = agent.deskCoord.y * this.tileSize;

      if (
        this.mouseWorld.x >= ax - 16 &&
        this.mouseWorld.x <= ax + 48 &&
        this.mouseWorld.y >= ay - 20 &&
        this.mouseWorld.y <= ay + 44
      ) {
        this.hoveredAgent = agent;
        break;
      }
    }

    if (this.hoveredAgent && tooltip) {
      const sp = this.worldToScreen(
        this.hoveredAgent.deskCoord.x * this.tileSize,
        this.hoveredAgent.deskCoord.y * this.tileSize
      );
      tooltip.style.left = `${sp.x + 20}px`;
      tooltip.style.top = `${sp.y - 40}px`;
      document.getElementById('tooltip-name').textContent = `@${this.hoveredAgent.id}`;
      document.getElementById('tooltip-role').textContent = this.hoveredAgent.role;
      document.getElementById('tooltip-status').textContent = `STATUS: ${this.hoveredAgent.status}`;
      tooltip.classList.remove('hidden');
    } else if (tooltip) {
      tooltip.classList.add('hidden');
    }
  }

  start() {
    const loop = () => {
      this.update();
      this.render();
      requestAnimationFrame(loop);
    };
    requestAnimationFrame(loop);
  }

  update() {
    // Interpolação suave da câmera (Lerp)
    this.camera.x += (this.camera.targetX - this.camera.x) * 0.1;
    this.camera.y += (this.camera.targetY - this.camera.y) * 0.1;
    this.camera.zoom += (this.camera.targetZoom - this.camera.zoom) * 0.1;

    // Atualiza partículas
    this.particles.forEach(p => {
      p.x += p.vx;
      p.y += p.vy;
      p.vy += 0.15; // gravidade
      p.life -= p.decay;
    });
    this.particles = this.particles.filter(p => p.life > 0);

    // Atualiza Envelopes de Handoff
    this.flyingHandoffs.forEach(h => {
      h.progress += h.speed;
      h.x = h.startX + (h.targetX - h.startX) * h.progress;
      // Curva parabólica suave
      const arc = Math.sin(h.progress * Math.PI) * -60;
      h.y = h.startY + (h.targetY - h.startY) * h.progress + arc;

      // Adiciona rastro luminoso
      h.trail.push({ x: h.x, y: h.y, life: 1.0 });
      h.trail.forEach(t => t.life -= 0.08);
      h.trail = h.trail.filter(t => t.life > 0);
    });
    this.flyingHandoffs = this.flyingHandoffs.filter(h => h.progress < 1.0);
  }

  render() {
    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

    this.ctx.save();
    this.ctx.translate(this.canvas.width / 2, this.canvas.height / 2);
    this.ctx.scale(this.camera.zoom, this.camera.zoom);
    this.ctx.translate(-this.camera.x, -this.camera.y);

    // 1. Base Unificada de Carvalho e Carpetes Temáticos por Setor
    this.renderFloors();

    // 2. Paredes Perimetrais e Divisórias de Vidro/Metal
    this.renderWalls();

    // 3. Placas de Identificação dos Setores em Pixel Art
    this.renderSectorSigns();

    // 4. Mobília, Estações e Área de Café/Bebedouro
    this.renderFurniture();

    // 5. Avatares dos Agentes
    this.renderAgents();

    // 6. Partículas e Handoffs Voadores
    this.renderEffects();

    this.ctx.restore();
  }

  renderFloors() {
    const floorWood = this.sprites.get('floor_wood');
    const carpetGov = this.sprites.get('carpet_governance');
    const carpetOrch = this.sprites.get('carpet_orchestration');
    const floorBunker = this.sprites.get('floor_bunker');
    const floorCaution = this.sprites.get('floor_caution');
    const carpetFront = this.sprites.get('carpet_frontline');
    const floorQA = this.sprites.get('floor_qa');
    const carpetLounge = this.sprites.get('carpet_lounge');

    for (let c = 0; c < this.mapCols; c++) {
      for (let r = 0; r < this.mapRows; r++) {
        const x = c * this.tileSize;
        const y = r * this.tileSize;

        let tile = floorWood; // Piso base de madeira executiva acolhedora

        // Sala 1 (Noroeste): Governança & Arquitetura
        if (c >= 2 && c <= 16 && r >= 2 && r <= 9) {
          tile = carpetGov;
        }
        // Sala 2 (Norte Central): Comando & Orquestração
        else if (c >= 18 && c <= 27 && r >= 2 && r <= 9) {
          tile = carpetOrch;
        }
        // Sala 3 (Nordeste): Bunker de Segurança OWASP
        else if (c >= 29 && c <= 43 && r >= 2 && r <= 9) {
          tile = floorBunker;
        }
        // Faixa de advertência na entrada do Bunker
        else if ((c === 35 || c === 36) && r === 10) {
          tile = floorCaution;
        }
        // Lounge Central
        else if (c >= 19 && c <= 26 && r >= 11 && r <= 12) {
          tile = carpetLounge;
        }
        // Sala 4 (Sudoeste): Engenharia Frontline
        else if (c >= 2 && c <= 21 && r >= 14 && r <= 23) {
          tile = carpetFront;
        }
        // Sala 5 (Sudeste): Laboratório QA & Performance
        else if (c >= 24 && c <= 43 && r >= 14 && r <= 23) {
          tile = floorQA;
        }

        if (tile) this.ctx.drawImage(tile, x, y);
      }
    }
  }

  renderWalls() {
    const partition = this.sprites.get('wall_partition');

    // 1. Paredes Externas Estruturais
    this.ctx.fillStyle = '#0b0f19';
    this.ctx.fillRect(0, 0, this.mapCols * this.tileSize, 10);
    this.ctx.fillRect(0, 0, 10, this.mapRows * this.tileSize);
    this.ctx.fillRect(this.mapCols * this.tileSize - 10, 0, 10, this.mapRows * this.tileSize);
    this.ctx.fillRect(0, this.mapRows * this.tileSize - 10, this.mapCols * this.tileSize, 10);

    // 2. Divisórias de Vidro Fumê e Metal (1 tile de altura com aberturas de portas)
    const isPartitionTile = (c, r) => {
      // Divisória horizontal Norte (row 10): portas em (9,10), (22,23), (35,36)
      if (r === 10) {
        if (c >= 1 && c <= 8) return true;
        if (c >= 11 && c <= 16) return true;
        if (c === 17) return true; // pilar
        if (c >= 18 && c <= 21) return true;
        if (c >= 24 && c <= 27) return true;
        if (c === 28) return true; // pilar
        if (c >= 29 && c <= 34) return true;
        if (c >= 37 && c <= 44) return true;
        return false;
      }

      // Divisória vertical Norte (Governança | Comando): col 17, rows 1-9
      if (c === 17 && r >= 1 && r <= 9) return true;

      // Divisória vertical Norte (Comando | Bunker): col 28, rows 1-9
      if (c === 28 && r >= 1 && r <= 9) return true;

      // Divisória horizontal Sul (row 13): portas em (11,12), (33,34)
      if (r === 13) {
        if (c >= 1 && c <= 10) return true;
        if (c >= 13 && c <= 21) return true;
        if (c === 22 || c === 23) return true; // pilar
        if (c >= 24 && c <= 32) return true;
        if (c >= 35 && c <= 44) return true;
        return false;
      }

      // Divisória vertical Sul (Engenharia | QA Lab): col 22-23, rows 14-24
      if ((c === 22 || c === 23) && r >= 14 && r <= 24) return true;

      return false;
    };

    if (partition) {
      for (let c = 0; c < this.mapCols; c++) {
        for (let r = 0; r < this.mapRows; r++) {
          if (isPartitionTile(c, r)) {
            this.ctx.drawImage(partition, c * this.tileSize, r * this.tileSize);
          }
        }
      }
    }
  }

  renderSectorSigns() {
    const signs = [
      {
        text: '🏛️ GOVERNANÇA & ARQUITETURA',
        x: 9 * this.tileSize,
        y: 1.5 * this.tileSize,
        borderColor: '#10b981',
        textColor: '#6ee7b7'
      },
      {
        text: '🛸 COMANDO ANTIGRAVITY',
        x: 22.5 * this.tileSize,
        y: 1.5 * this.tileSize,
        borderColor: '#a855f7',
        textColor: '#d8b4fe'
      },
      {
        text: '🛡️ BUNKER DE SEGURANÇA',
        x: 36 * this.tileSize,
        y: 1.5 * this.tileSize,
        borderColor: '#ef4444',
        textColor: '#fca5a5'
      },
      {
        text: '⚡ ENGENHARIA FRONTLINE',
        x: 11.5 * this.tileSize,
        y: 14.5 * this.tileSize,
        borderColor: '#3b82f6',
        textColor: '#93c5fd'
      },
      {
        text: '🧪 LABORATÓRIO QA & DESIGN',
        x: 34 * this.tileSize,
        y: 14.5 * this.tileSize,
        borderColor: '#14b8a6',
        textColor: '#5eead4'
      }
    ];

    signs.forEach(sign => {
      this.ctx.font = 'bold 8px "Press Start 2P", monospace';
      const textMetrics = this.ctx.measureText(sign.text);
      const padX = 8;
      const boxW = textMetrics.width + padX * 2;
      const boxH = 18;
      const startX = Math.round(sign.x - boxW / 2);
      const startY = Math.round(sign.y - boxH / 2);

      // Sombra
      this.ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
      this.ctx.fillRect(startX + 2, startY + 2, boxW, boxH);

      // Fundo escuro com leve translucidez
      this.ctx.fillStyle = '#0f172a';
      this.ctx.fillRect(startX, startY, boxW, boxH);

      // Moldura temática
      this.ctx.strokeStyle = sign.borderColor;
      this.ctx.lineWidth = 1;
      this.ctx.strokeRect(startX + 0.5, startY + 0.5, boxW - 1, boxH - 1);

      // Rebites de canto
      this.ctx.fillStyle = sign.borderColor;
      this.ctx.fillRect(startX + 1, startY + 1, 2, 2);
      this.ctx.fillRect(startX + boxW - 3, startY + 1, 2, 2);
      this.ctx.fillRect(startX + 1, startY + boxH - 3, 2, 2);
      this.ctx.fillRect(startX + boxW - 3, startY + boxH - 3, 2, 2);

      // Texto renderizado
      this.ctx.fillStyle = sign.textColor;
      this.ctx.textAlign = 'center';
      this.ctx.textBaseline = 'middle';
      this.ctx.fillText(sign.text, sign.x, sign.y + 1);
    });
  }

  renderFurniture() {
    const desk = this.sprites.get('desk');
    const dualMon = this.sprites.get('dual_monitors');
    const rack = this.sprites.get('server_rack');
    const plant = this.sprites.get('plant');
    const bookshelf = this.sprites.get('bookshelf');
    const coffee = this.sprites.get('coffee_machine');
    const water = this.sprites.get('water_cooler');

    // 1. Racks de Servidores no Bunker de Segurança
    if (rack) {
      this.ctx.drawImage(rack, 30 * this.tileSize, 2 * this.tileSize);
      this.ctx.drawImage(rack, 41 * this.tileSize, 2 * this.tileSize);
    }

    // 2. Estantes na Governança
    if (bookshelf) {
      this.ctx.drawImage(bookshelf, 2 * this.tileSize, 2 * this.tileSize);
      this.ctx.drawImage(bookshelf, 15 * this.tileSize, 2 * this.tileSize);
    }

    // 3. Área de Convivência / Lounge Central
    if (water) {
      this.ctx.drawImage(water, 18 * this.tileSize, 11 * this.tileSize);
    }
    if (coffee) {
      this.ctx.drawImage(coffee, 21 * this.tileSize, 11 * this.tileSize);
    }

    // 4. Plantas Ornamentais nos cantos dos setores
    if (plant) {
      this.ctx.drawImage(plant, 3 * this.tileSize, 9 * this.tileSize); // Governança
      this.ctx.drawImage(plant, 26 * this.tileSize, 11 * this.tileSize); // Lounge
      this.ctx.drawImage(plant, 3 * this.tileSize, 22 * this.tileSize); // Engenharia
      this.ctx.drawImage(plant, 42 * this.tileSize, 22 * this.tileSize); // QA Lab
    }

    // 5. Mesas de Trabalho de Cada Agente
    this.agents.forEach(agent => {
      const x = agent.deskCoord.x * this.tileSize;
      const y = agent.deskCoord.y * this.tileSize;

      if (desk) this.ctx.drawImage(desk, x - 16, y);
      if (dualMon) this.ctx.drawImage(dualMon, x - 8, y - 6);
    });
  }

  renderAgents() {
    const now = Date.now();
    const animTick = Math.floor(now / 300) % 2;

    this.agents.forEach(agent => {
      const x = agent.deskCoord.x * this.tileSize;
      const y = agent.deskCoord.y * this.tileSize;

      // Define frame de animação conforme estado
      let anim = `idle_${animTick}`;
      if (agent.status === 'RUNNING') {
        anim = `typing_${animTick}`;
      } else if (agent.status === 'PASS') {
        anim = 'pass';
      } else if (agent.status === 'REVISE') {
        anim = 'revise';
      }

      const sprite = this.sprites.get(`avatar_${agent.id}_${anim}`);
      if (sprite) {
        this.ctx.drawImage(sprite, x, y - 16);
      }

      // Balão de pensamento quando estiver em RUNNING
      if (agent.status === 'RUNNING') {
        this.ctx.fillStyle = '#0f172a';
        this.ctx.fillRect(x + 18, y - 32, 20, 16);
        this.ctx.strokeStyle = '#6ae49b';
        this.ctx.strokeRect(x + 18, y - 32, 20, 16);

        const toolIcon = this.sprites.get('tool_terminal');
        if (toolIcon) this.ctx.drawImage(toolIcon, x + 21, y - 30);
      }

      // Tag de Nome abaixo da mesa
      this.ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
      this.ctx.fillRect(x - 14, y + 36, 60, 12);
      this.ctx.fillStyle = agent.status === 'RUNNING' ? '#4ade80' : '#f8fafc';
      this.ctx.font = '7px "Press Start 2P", monospace';
      this.ctx.fillText(agent.badge, x - 8, y + 45);
    });
  }

  renderEffects() {
    // 1. Rastro e Envelope de Handoff
    this.flyingHandoffs.forEach(h => {
      h.trail.forEach(t => {
        this.ctx.fillStyle = `rgba(106, 228, 155, ${t.life * 0.6})`;
        this.ctx.fillRect(t.x - 2, t.y - 2, 4, 4);
      });

      // Envelope pixel art
      this.ctx.fillStyle = '#ffffff';
      this.ctx.fillRect(h.x - 6, h.y - 4, 12, 8);
      this.ctx.fillStyle = '#ef4444';
      this.ctx.fillRect(h.x - 1, h.y - 1, 2, 2);
    });

    // 2. Partículas (Confetes)
    this.particles.forEach(p => {
      this.ctx.fillStyle = p.color;
      this.ctx.globalAlpha = p.life;
      this.ctx.fillRect(p.x, p.y, p.size, p.size);
    });
    this.ctx.globalAlpha = 1.0;
  }
}
