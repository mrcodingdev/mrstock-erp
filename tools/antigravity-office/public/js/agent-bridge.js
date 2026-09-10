/**
 * ANTIGRAVITY OFFICE — AGENT BRIDGE (SSE CLIENT)
 * Gerencia o stream em tempo real (/api/stream) com reconexão resiliente.
 */

class AgentBridge {
  constructor() {
    this.eventSource = null;
    this.stateListeners = [];
    this.handoffListeners = [];
    this.auditListeners = [];
    this.isConnected = false;
  }

  connect() {
    if (this.eventSource) this.eventSource.close();

    this.eventSource = new EventSource('/api/stream');

    this.eventSource.onopen = () => {
      this.isConnected = true;
      const led = document.getElementById('server-led');
      if (led) led.className = 'server-led online';
    };

    this.eventSource.addEventListener('state', (e) => {
      try {
        const state = JSON.parse(e.data);
        this.stateListeners.forEach(cb => cb(state));
      } catch (err) {
        console.error('[AgentBridge] Erro ao parsear state:', err);
      }
    });

    this.eventSource.addEventListener('handoff', (e) => {
      try {
        const handoff = JSON.parse(e.data);
        this.handoffListeners.forEach(cb => cb(handoff));
      } catch (err) {
        console.error('[AgentBridge] Erro ao parsear handoff:', err);
      }
    });

    this.eventSource.addEventListener('audit', (e) => {
      try {
        const audit = JSON.parse(e.data);
        this.auditListeners.forEach(cb => cb(audit));
      } catch (err) {
        console.error('[AgentBridge] Erro ao parsear audit:', err);
      }
    });

    this.eventSource.onerror = () => {
      this.isConnected = false;
      const led = document.getElementById('server-led');
      if (led) led.className = 'server-led';
    };
  }

  onState(callback) { this.stateListeners.push(callback); }
  onHandoff(callback) { this.handoffListeners.push(callback); }
  onAudit(callback) { this.auditListeners.push(callback); }

  async fetchTranscript(agentName) {
    const res = await fetch(`/api/agent/${encodeURIComponent(agentName)}/transcript`);
    return await res.json();
  }

  async sendInteraction(agentName, message) {
    const res = await fetch(`/api/agent/${encodeURIComponent(agentName)}/interact`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message, sender: 'Douglas (Dev)' })
    });
    return await res.json();
  }
}

window.agentBridge = new AgentBridge();
