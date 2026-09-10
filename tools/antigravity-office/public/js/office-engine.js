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

    this.setupListeners();
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

  resize() {
    this.canvas.width = this.canvas.parentElement.clientWidth;
    this.canvas.height = this.canvas.parentElement.clientHeight;
    this.ctx.imageSmoothingEnabled = false;
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

    // 1. Renderiza os Pisos das 5 Salas
    this.renderFloors();

    // 2. Paredes e Divisórias
    this.renderWalls();

    // 3. Mobília e Estações
    this.renderFurniture();

    // 4. Avatares dos Agentes
    this.renderAgents();

    // 5. Partículas e Handoffs
    this.renderEffects();

    this.ctx.restore();
  }

  renderFloors() {
    for (let c = 0; c < this.mapCols; c++) {
      for (let r = 0; r < this.mapRows; r++) {
        const x = c * this.tileSize;
        const y = r * this.tileSize;

        let tile = this.sprites.get('floor_wood');
        // Lounge Central com Carpete Verde Papelaria Real
        if (c >= 18 && c <= 26 && r >= 10 && r <= 16) {
          tile = this.sprites.get('floor_carpet');
        } else if (c >= 26 && r >= 14) {
          // Lab de QA
          tile = this.sprites.get('floor_lab');
        } else if (c >= 28 && r <= 10) {
          // Bunker
          tile = this.sprites.get('floor_bunker');
        }

        if (tile) this.ctx.drawImage(tile, x, y);
      }
    }
  }

  renderWalls() {
    this.ctx.fillStyle = '#0f172a';
    // Paredes externas
    this.ctx.fillRect(0, 0, this.mapCols * this.tileSize, 8);
    this.ctx.fillRect(0, 0, 8, this.mapRows * this.tileSize);
    this.ctx.fillRect(this.mapCols * this.tileSize - 8, 0, 8, this.mapRows * this.tileSize);
    this.ctx.fillRect(0, this.mapRows * this.tileSize - 8, this.mapCols * this.tileSize, 8);
  }

  renderFurniture() {
    const desk = this.sprites.get('desk');
    const dualMon = this.sprites.get('dual_monitors');
    const rack = this.sprites.get('server_rack');
    const plant = this.sprites.get('plant');
    const bookshelf = this.sprites.get('bookshelf');

    // Racks no Bunker de Segurança
    if (rack) {
      this.ctx.drawImage(rack, 30 * this.tileSize, 2 * this.tileSize);
      this.ctx.drawImage(rack, 38 * this.tileSize, 2 * this.tileSize);
    }

    // Estantes na Governança
    if (bookshelf) {
      this.ctx.drawImage(bookshelf, 2 * this.tileSize, 2 * this.tileSize);
    }

    // Plantas nos cantos do lounge
    if (plant) {
      this.ctx.drawImage(plant, 17 * this.tileSize, 10 * this.tileSize);
      this.ctx.drawImage(plant, 27 * this.tileSize, 10 * this.tileSize);
    }

    // Mesas de cada agente
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
