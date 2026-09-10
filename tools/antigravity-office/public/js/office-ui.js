/**
 * ANTIGRAVITY OFFICE — UI & INTERACTION CONTROLLER
 * Orquestra Canvas, Drawer, Áudio e SSE
 */

class OfficeUI {
  constructor() {
    this.engine = new OfficeEngine('office-canvas');
    this.bridge = window.agentBridge;
    this.audio = window.audioFx;
    this.selectedAgent = null;

    this.activeTab = 'tab-transcripts';
    this.initHUD();
    this.initDrawer();
    this.initTicker();
    this.initKeyboardShortcuts();
    this.initBridgeEvents();

    this.engine.start();
  }

  initHUD() {
    // Alternar áudio
    const btnAudio = document.getElementById('btn-audio-toggle');
    if (btnAudio) {
      const updateAudioBtn = (muted) => {
        btnAudio.innerHTML = `<span id="audio-icon">${muted ? '🔇' : '🔊'}</span> SFX: ${muted ? 'MUDO' : 'LIGADO'}`;
        if (muted) {
          btnAudio.classList.add('muted');
        } else {
          btnAudio.classList.remove('muted');
        }
      };
      // Inicializa o botão no carregamento com o estado real persistido
      updateAudioBtn(this.audio.isMuted);

      btnAudio.addEventListener('click', () => {
        const isMuted = this.audio.toggleMute();
        updateAudioBtn(isMuted);
      });
    }

    // Botão FIT
    const btnFit = document.getElementById('btn-fit-screen');
    if (btnFit) {
      btnFit.addEventListener('click', () => {
        this.engine.fitToScreen();
      });
    }

    // Zoom e Câmera
    document.getElementById('btn-zoom-in').addEventListener('click', () => {
      this.engine.camera.targetZoom = Math.min(2.5, this.engine.camera.targetZoom + 0.25);
    });
    document.getElementById('btn-zoom-out').addEventListener('click', () => {
      this.engine.camera.targetZoom = Math.max(0.5, this.engine.camera.targetZoom - 0.25);
    });
    document.getElementById('btn-reset-cam').addEventListener('click', () => {
      this.engine.camera.targetX = 736;
      this.engine.camera.targetY = 416;
      this.engine.camera.targetZoom = 1.0;
    });
    document.getElementById('btn-focus-active').addEventListener('click', () => {
      this.focusActiveAgent();
    });
  }

  initKeyboardShortcuts() {
    window.addEventListener('keydown', (e) => {
      const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
      if (activeTag === 'input' || activeTag === 'textarea') {
        return;
      }

      if (e.code === 'KeyF') {
        e.preventDefault();
        this.engine.fitToScreen();
      } else if (e.code === 'KeyM') {
        e.preventDefault();
        const btnAudio = document.getElementById('btn-audio-toggle');
        if (btnAudio) btnAudio.click();
      } else if (e.code === 'Space') {
        e.preventDefault();
        this.focusActiveAgent();
      } else if (e.code === 'Escape') {
        const drawer = document.getElementById('agent-drawer');
        if (drawer && !drawer.classList.contains('closed')) {
          drawer.classList.add('closed');
        }
      } else if (e.code === 'Digit1') {
        e.preventDefault();
        this.engine.panToSector('governance');
      } else if (e.code === 'Digit2') {
        e.preventDefault();
        this.engine.panToSector('orchestration');
      } else if (e.code === 'Digit3') {
        e.preventDefault();
        this.engine.panToSector('bunker');
      } else if (e.code === 'Digit4') {
        e.preventDefault();
        this.engine.panToSector('development');
      } else if (e.code === 'Digit5') {
        e.preventDefault();
        this.engine.panToSector('qa_lab');
      }
    });
  }

  initTicker() {
    const timeEl = document.getElementById('ticker-time');
    const updateTime = () => {
      if (!timeEl) return;
      const now = new Date();
      const hh = String(now.getHours()).padStart(2, '0');
      const mm = String(now.getMinutes()).padStart(2, '0');
      const ss = String(now.getSeconds()).padStart(2, '0');
      timeEl.textContent = `[${hh}:${mm}:${ss}]`;
    };
    updateTime();
    setInterval(updateTime, 1000);

    const sectorChips = document.querySelectorAll('.chip-sector');
    sectorChips.forEach(chip => {
      chip.addEventListener('click', () => {
        const sector = chip.getAttribute('data-sector');
        if (sector) {
          this.engine.panToSector(sector);
        }
      });
    });
  }

  initDrawer() {
    const drawer = document.getElementById('agent-drawer');
    const btnClose = document.getElementById('btn-close-drawer');
    const form = document.getElementById('interaction-form');

    if (btnClose) {
      btnClose.addEventListener('click', () => {
        drawer.classList.add('closed');
      });
    }

    // Alternância de Abas do Cockpit
    const tabButtons = document.querySelectorAll('.cockpit-tab-btn');
    tabButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const targetTab = btn.getAttribute('data-tab');
        this.switchCockpitTab(targetTab);
      });
    });

    // Botões de Refresh de Transcripts e Thinking
    const btnRefreshThinking = document.getElementById('btn-refresh-thinking');
    if (btnRefreshThinking) {
      btnRefreshThinking.addEventListener('click', () => {
        if (this.selectedAgent) this.loadThinking(this.selectedAgent.id);
      });
    }

    const btnRefreshTranscript = document.getElementById('btn-refresh-transcript');
    if (btnRefreshTranscript) {
      btnRefreshTranscript.addEventListener('click', () => {
        if (this.selectedAgent) this.loadTranscript(this.selectedAgent.id);
      });
    }

    // Botões de Prompts Rápidos em 1 Clique
    const quickButtons = document.querySelectorAll('.btn-quick-prompt');
    quickButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const prompt = btn.getAttribute('data-prompt');
        const input = document.getElementById('interaction-input');
        if (input && prompt) {
          input.value = prompt;
          input.focus();
        }
      });
    });

    // Formulário de Transmissão ao Agente Pai (Maestro)
    if (form) {
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = document.getElementById('interaction-input');
        const feedback = document.getElementById('interaction-feedback');
        const text = input.value.trim();

        if (!text || !this.selectedAgent) return;

        try {
          const res = await this.bridge.sendOrchestratorCommand(text, this.selectedAgent.id);
          feedback.textContent = res.message || 'Comando transmitido ao Agente Pai (Maestro)!';
          feedback.classList.remove('hidden');
          input.value = '';
          setTimeout(() => feedback.classList.add('hidden'), 4000);
          this.loadTranscript(this.selectedAgent.id);
          this.loadThinking(this.selectedAgent.id);
        } catch (err) {
          feedback.textContent = 'Erro ao transmitir comando.';
          feedback.classList.remove('hidden');
        }
      });
    }
  }

  switchCockpitTab(tabId) {
    this.activeTab = tabId;
    const tabButtons = document.querySelectorAll('.cockpit-tab-btn');
    tabButtons.forEach(btn => {
      if (btn.getAttribute('data-tab') === tabId) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
    });

    const panes = document.querySelectorAll('.tab-pane');
    panes.forEach(pane => {
      if (pane.id === tabId) {
        pane.classList.remove('hidden');
        pane.classList.add('active');
      } else {
        pane.classList.add('hidden');
        pane.classList.remove('active');
      }
    });

    if (this.selectedAgent) {
      if (tabId === 'tab-thinking') {
        this.loadThinking(this.selectedAgent.id);
      } else if (tabId === 'tab-metrics') {
        this.updateMetricsTab(this.selectedAgent);
      } else if (tabId === 'tab-transcripts') {
        this.loadTranscript(this.selectedAgent.id);
      }
    }
  }

  initBridgeEvents() {
    const tickerFeed = document.getElementById('ticker-feed');
    const setTickerFeed = (msg) => {
      if (tickerFeed) tickerFeed.textContent = msg;
    };

    this.bridge.onState((state) => {
      document.getElementById('active-agents-count').textContent = `${state.activeCount} / ${state.totalAgents}`;
      document.getElementById('pass-count').textContent = state.auditCounters.pass;
      document.getElementById('revise-count').textContent = state.auditCounters.revise;

      // Atualiza widget de steps globais no HUD
      if (state.globalMetrics && typeof state.globalMetrics.totalGlobalSteps === 'number') {
        const stepsCountEl = document.getElementById('global-steps-count');
        if (stepsCountEl) {
          stepsCountEl.textContent = state.globalMetrics.totalGlobalSteps.toLocaleString('pt-BR');
        }
      }

      if (state.activeCount > 0) {
        setTickerFeed(`Atividade detectada: ${state.activeCount} agente(s) em execução paralela.`);
      } else {
        setTickerFeed('Sistema em repouso. Todos os agentes prontos para despacho.');
      }

      this.engine.updateAgents(state.agents);

      // Atualiza cockpit se o agente estiver aberto
      if (this.selectedAgent) {
        const updated = state.agents.find(a => a.id === this.selectedAgent.id);
        if (updated) {
          this.selectedAgent = updated;
          this.updateDrawerDetails(updated);
        }
      }
    });

    this.bridge.onHandoff((handoff) => {
      this.engine.triggerHandoff(handoff);
      setTickerFeed(`Handoff em trânsito: @${handoff.from} ➔ @${handoff.to} (${handoff.task || 'despacho de tarefa'})`);
    });

    this.bridge.onAudit((audit) => {
      if (audit.type === 'PASS') {
        this.audio.playPassFanfare();
        this.engine.spawnConfetti(this.engine.camera.x, this.engine.camera.y - 100);
        setTickerFeed(`Auditoria PASS aprovada para @${audit.agentId || 'agente'}! Parabéns pelo Clean Code.`);
      } else {
        this.audio.playReviseAlert();
        setTickerFeed(`Alerta REVISE disparado para @${audit.agentId || 'agente'}. Correções solicitadas.`);
      }
    });

    this.bridge.connect();
  }

  openAgentCockpit(agent) {
    this.selectedAgent = agent;
    const drawer = document.getElementById('agent-drawer');
    drawer.classList.remove('closed');

    const titleEl = document.getElementById('drawer-transmission-title');
    if (titleEl) {
      if (agent.id === 'antigravity-orchestrator') {
        titleEl.textContent = '📡 TRANSMITIR COMANDO AO AGENTE PAI (MAESTRO)';
      } else {
        titleEl.textContent = `📡 DESPACHAR VIA AGENTE PAI PARA @${agent.id.toUpperCase()}`;
      }
    }

    this.updateDrawerDetails(agent);

    if (this.activeTab === 'tab-thinking') {
      this.loadThinking(agent.id);
    } else if (this.activeTab === 'tab-metrics') {
      this.updateMetricsTab(agent);
    } else {
      this.loadTranscript(agent.id);
    }
  }

  updateDrawerDetails(agent) {
    document.getElementById('drawer-agent-name').textContent = `@${agent.id}`;
    document.getElementById('drawer-agent-role').textContent = agent.role;
    document.getElementById('drawer-agent-level').textContent = `LVL ${agent.level} • ESPECIALISTA`;
    document.getElementById('drawer-agent-room').textContent = `SALA: ${agent.room.toUpperCase()}`;

    const badge = document.getElementById('drawer-status-badge');
    badge.textContent = `STATUS: ${agent.status}`;
    badge.style.color = agent.status === 'RUNNING' ? '#4ade80' : '#f8fafc';

    document.getElementById('drawer-tool-action').textContent = agent.toolAction;
    document.getElementById('drawer-stat-tools').textContent = (agent.stats && agent.stats.toolsUsed) || 0;
    document.getElementById('drawer-stat-pass').textContent = (agent.stats && agent.stats.passCount) || 0;
    document.getElementById('drawer-stat-revise').textContent = (agent.stats && agent.stats.reviseCount) || 0;

    // Atualiza aba de métricas se estiver aberta
    this.updateMetricsTab(agent);

    // Renderiza avatar 4x no mini canvas do Drawer
    const avatarCanvas = document.getElementById('drawer-avatar-canvas');
    const ctx = avatarCanvas.getContext('2d');
    ctx.clearRect(0, 0, 64, 64);
    ctx.imageSmoothingEnabled = false;

    const sprite = window.officeSprites.get(`avatar_${agent.id}_idle_0`);
    if (sprite) {
      ctx.drawImage(sprite, 0, 0, 32, 32, 0, 0, 64, 64);
    }
  }

  updateMetricsTab(agent) {
    const stepsEl = document.getElementById('metric-agent-steps');
    const toolsEl = document.getElementById('metric-agent-tools');
    const staminaFill = document.getElementById('metric-stamina-fill');
    const staminaText = document.getElementById('metric-stamina-text');
    const lastActivityEl = document.getElementById('metric-last-activity');

    const steps = (agent.stats && agent.stats.stepsCount) ? agent.stats.stepsCount : ((agent.stats && agent.stats.toolsUsed) ? agent.stats.toolsUsed * 2 : 0);
    const tools = (agent.stats && agent.stats.toolsUsed) ? agent.stats.toolsUsed : 0;

    if (stepsEl) stepsEl.textContent = steps;
    if (toolsEl) toolsEl.textContent = tools;

    // Cálculo dinâmico de estamina operacional
    const staminaPct = Math.max(20, Math.min(100, 100 - (steps % 80) * 0.8));
    if (staminaFill) staminaFill.style.width = `${staminaPct}%`;
    if (staminaText) {
      if (agent.status === 'RUNNING') {
        staminaText.textContent = `${Math.round(staminaPct)}% • EXECUTANDO NO WORKSPACE`;
        staminaText.style.color = '#4ade80';
      } else {
        staminaText.textContent = `${Math.round(staminaPct)}% • OPERACIONAL`;
        staminaText.style.color = '#f8fafc';
      }
    }

    if (lastActivityEl) {
      if (agent.lastUpdated) {
        const diffSec = Math.round((Date.now() - agent.lastUpdated) / 1000);
        if (diffSec < 15) {
          lastActivityEl.textContent = 'Agora mesmo (< 15s)';
        } else if (diffSec < 60) {
          lastActivityEl.textContent = `Há ${diffSec} segundos`;
        } else {
          lastActivityEl.textContent = new Date(agent.lastUpdated).toLocaleTimeString();
        }
      } else {
        lastActivityEl.textContent = 'Sem registros recentes';
      }
    }
  }

  async loadThinking(agentId) {
    const container = document.getElementById('thinking-stream-content');
    if (!container) return;
    try {
      const res = await this.bridge.fetchThinking(agentId);
      container.innerHTML = '';
      if (!res.thinking || res.thinking.length === 0) {
        container.innerHTML = '<div class="thinking-placeholder">Nenhum bloco de raciocínio registrado ainda para este agente.</div>';
        return;
      }

      res.thinking.forEach(block => {
        const card = document.createElement('div');
        card.className = 'thinking-card';

        const timeStr = block.timestamp ? new Date(block.timestamp).toLocaleTimeString() : '';
        const toolsStr = block.toolCalls && block.toolCalls.length > 0
          ? `🔧 Ação planejada: ${block.toolCalls.join(', ')}`
          : '💭 Deliberação e planejamento estratégico';

        // Formatação simples de Markdown
        let formattedText = (block.thinking || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
          .replace(/^### (.*$)/gim, '<strong style="color:#a3f7bf;">$1</strong>')
          .replace(/^## (.*$)/gim, '<strong style="color:#facc15;">$1</strong>')
          .replace(/^# (.*$)/gim, '<strong style="color:#f59e0b;">$1</strong>');

        card.innerHTML = `
          <div class="thinking-card-head">
            <span class="thinking-step-badge">STEP #${block.stepIndex}</span>
            <span class="thinking-time">${timeStr}</span>
          </div>
          <div class="thinking-tools-planned">${toolsStr}</div>
          <div class="thinking-card-text">${formattedText}</div>
        `;
        container.appendChild(card);
      });
      container.scrollTop = container.scrollHeight;
    } catch (err) {
      container.innerHTML = '<div class="thinking-placeholder text-revise">Erro ao carregar pensamentos em tempo real.</div>';
    }
  }

  async loadTranscript(agentId) {
    const terminal = document.getElementById('drawer-transcript-terminal');
    try {
      const data = await this.bridge.fetchTranscript(agentId);
      terminal.innerHTML = '';
      data.lines.forEach(line => {
        const div = document.createElement('div');
        div.className = 'log-line';
        div.textContent = line;
        terminal.appendChild(div);
      });
      terminal.scrollTop = terminal.scrollHeight;
    } catch (e) {
      terminal.innerHTML = '<div class="log-line text-revise">[ERRO] Falha ao carregar transcript.</div>';
    }
  }

  focusActiveAgent() {
    const active = this.engine.agents.find(a => a.status === 'RUNNING');
    if (active) {
      this.engine.camera.targetX = active.deskCoord.x * this.engine.tileSize;
      this.engine.camera.targetY = active.deskCoord.y * this.engine.tileSize;
      this.engine.camera.targetZoom = 1.6;
    }
  }
}

window.addEventListener('DOMContentLoaded', () => {
  window.officeUI = new OfficeUI();
});
