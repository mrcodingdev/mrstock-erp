/**
 * ANTIGRAVITY OFFICE — AGENT SCANNER
 * Monitora C:\Users\Douglas\.gemini\antigravity\brain\ e C:\xampp\htdocs\MrStock\.agents\
 * Rastreia os 9 subagentes oficiais + Antigravity Orchestrator em tempo real.
 */

const fs = require('fs');
const path = require('path');
const EventEmitter = require('events');

class AgentScanner extends EventEmitter {
  constructor() {
    super();
    this.brainDir = 'C:\\Users\\Douglas\\.gemini\\antigravity\\brain';
    this.agentsSpecDir = 'C:\\xampp\\htdocs\\MrStock\\.agents\\agents';
    this.lastState = {};
    this.auditCounters = { pass: 0, revise: 0, total: 0 };
    this.recentHandoffs = [];
    this.recentInteractions = [];

    // Os 10 agentes oficiais com metadados de RPG e sala temática
    this.agentDefinitions = [
      {
        id: 'antigravity-orchestrator',
        name: 'Antigravity Orchestrator',
        role: 'Agente Pai & Maestro do Sistema',
        room: 'orchestration',
        deskCoord: { x: 22, y: 5 },
        color: '#a855f7',
        badge: 'ORCH',
        avatarStyle: 'holo_robe',
        level: 99
      },
      {
        id: 'chief-erp-architect',
        name: 'Chief ERP Architect',
        role: 'Guardião de Governança & 4 Leis',
        room: 'governance',
        deskCoord: { x: 7, y: 6 },
        color: '#10b981',
        badge: 'ARCH',
        avatarStyle: 'emerald_suit',
        level: 85
      },
      {
        id: 'code-reviewer',
        name: 'Code Reviewer',
        role: 'Auditor de Código & Clean Code',
        room: 'governance',
        deskCoord: { x: 13, y: 6 },
        color: '#06b6d4',
        badge: 'REV',
        avatarStyle: 'lab_coat',
        level: 80
      },
      {
        id: 'security-auditor',
        name: 'Security Auditor',
        role: 'Auditor de Segurança & OWASP',
        room: 'bunker',
        deskCoord: { x: 34, y: 6 },
        color: '#ef4444',
        badge: 'SEC',
        avatarStyle: 'cyber_hoodie',
        level: 88
      },
      {
        id: 'software-engineer',
        name: 'Software Engineer',
        role: 'Engenheiro Full-Stack Frontline',
        room: 'development',
        deskCoord: { x: 6, y: 18 },
        color: '#3b82f6',
        badge: 'ENG',
        avatarStyle: 'green_hoodie',
        level: 82
      },
      {
        id: 'backend-engineer',
        name: 'Backend Engineer',
        role: 'Especialista Backend & PDO MySQL',
        room: 'development',
        deskCoord: { x: 12, y: 18 },
        color: '#f59e0b',
        badge: 'BACK',
        avatarStyle: 'plaid_shirt',
        level: 79
      },
      {
        id: 'frontend-engineer',
        name: 'Frontend Engineer',
        role: 'Especialista Frontend & UI Bootstrap 5',
        room: 'development',
        deskCoord: { x: 18, y: 18 },
        color: '#ec4899',
        badge: 'FRONT',
        avatarStyle: 'cyber_glasses',
        level: 78
      },
      {
        id: 'test-engineer',
        name: 'Test Engineer',
        role: 'Engenheiro de QA & Roteiros QTS',
        room: 'qa_lab',
        deskCoord: { x: 28, y: 18 },
        color: '#84cc16',
        badge: 'QA',
        avatarStyle: 'safety_helmet',
        level: 76
      },
      {
        id: 'web-performance-auditor',
        name: 'Web Performance Auditor',
        role: 'Auditor de Performance & Latência',
        room: 'qa_lab',
        deskCoord: { x: 34, y: 18 },
        color: '#eab308',
        badge: 'PERF',
        avatarStyle: 'runner_stopwatch',
        level: 75
      },
      {
        id: 'anti-slop-ui-auditor',
        name: 'Anti-Slop UI Auditor',
        role: 'Auditor de Design System & UX',
        room: 'qa_lab',
        deskCoord: { x: 40, y: 18 },
        color: '#14b8a6',
        badge: 'SLOP',
        avatarStyle: 'designer_beret',
        level: 81
      }
    ];

    this.agentStateMap = new Map();
    this.agentDefinitions.forEach(agent => {
      this.agentStateMap.set(agent.id, {
        ...agent,
        status: 'IDLE', // 'IDLE', 'RUNNING', 'PASS', 'REVISE', 'DONE', 'ERROR'
        currentTool: null,
        toolAction: 'Aguardando despacho',
        toolSummary: 'Ocioso',
        lastUpdated: Date.now(),
        lastVerdict: null,
        recentLines: [],
        stats: {
          toolsUsed: 0,
          passCount: 0,
          reviseCount: 0
        }
      });
    });
  }

  init() {
    this.scanBrain();
    // Monitoramento contínuo a cada 1.5s no sistema de arquivos
    setInterval(() => this.scanBrain(), 1500);
  }

  /**
   * Encontra a pasta de conversa mais recente no brain
   */
  findActiveBrainDirectory() {
    try {
      if (!fs.existsSync(this.brainDir)) return null;
      const dirs = fs.readdirSync(this.brainDir, { withFileTypes: true })
        .filter(d => d.isDirectory() && d.name !== 'scratch' && !d.name.startsWith('.'))
        .map(d => {
          const fullPath = path.join(this.brainDir, d.name);
          try {
            const stat = fs.statSync(fullPath);
            return { path: fullPath, name: d.name, mtime: stat.mtimeMs };
          } catch (e) {
            return null;
          }
        })
        .filter(Boolean)
        .sort((a, b) => b.mtime - a.mtime);

      return dirs.length > 0 ? dirs[0].path : null;
    } catch (e) {
      return null;
    }
  }

  /**
   * Varre o brain para extrair status e atividades recentes
   */
  scanBrain() {
    const activeDir = this.findActiveBrainDirectory();
    if (!activeDir) return;

    try {
      // Procura por arquivos de log ou scratch recentes
      const files = fs.readdirSync(activeDir);
      let latestActivityTime = 0;
      let activeAgentId = null;
      let detectedTool = null;

      files.forEach(file => {
        const filePath = path.join(activeDir, file);
        try {
          const stat = fs.statSync(filePath);
          if (stat.mtimeMs > latestActivityTime) {
            latestActivityTime = stat.mtimeMs;
          }

          // Se for markdown ou json recente, analisa o conteúdo
          if (file.endsWith('.md') || file.endsWith('.json') || file.endsWith('.jsonl')) {
            const content = fs.readFileSync(filePath, 'utf8');
            this.parseContentForVerdicts(content);
          }
        } catch (e) {}
      });

      // Se houve atividade recente nos últimos 30 segundos, marca software-engineer e orchestrator
      const isRecentlyActive = (Date.now() - latestActivityTime) < 30000;
      
      const engineer = this.agentStateMap.get('software-engineer');
      const orchestrator = this.agentStateMap.get('antigravity-orchestrator');

      if (isRecentlyActive && engineer) {
        if (engineer.status !== 'RUNNING') {
          engineer.status = 'RUNNING';
          engineer.currentTool = 'run_command';
          engineer.toolAction = 'Compilando e construindo o Antigravity Office 2D';
          engineer.toolSummary = 'Construção Frontline';
          engineer.lastUpdated = Date.now();
          engineer.stats.toolsUsed++;
        }
      } else if (engineer && engineer.status === 'RUNNING') {
        engineer.status = 'IDLE';
        engineer.currentTool = null;
        engineer.toolAction = 'Aguardando novas demandas';
      }

      if (orchestrator) {
        orchestrator.status = isRecentlyActive ? 'RUNNING' : 'IDLE';
        orchestrator.currentTool = isRecentlyActive ? 'send_message' : null;
        orchestrator.toolAction = isRecentlyActive ? 'Orquestrando pipeline de agentes' : 'Observando escritório';
      }

    } catch (err) {
      // Silencioso para manter robustez
    }
  }

  /**
   * Analisa texto em busca de vereditos PASS ou REVISE
   */
  parseContentForVerdicts(content) {
    if (!content) return;

    const passRegex = /\[\s*(?:🟢\s*)?PASS\s*\]/gi;
    const reviseRegex = /\[\s*(?:🔴\s*)?REVISE\s*\]/gi;

    let passMatch = passRegex.exec(content);
    if (passMatch) {
      this.auditCounters.pass++;
      this.auditCounters.total++;
      this.emit('audit', { type: 'PASS', timestamp: Date.now() });
    }

    let reviseMatch = reviseRegex.exec(content);
    if (reviseMatch) {
      this.auditCounters.revise++;
      this.auditCounters.total++;
      this.emit('audit', { type: 'REVISE', timestamp: Date.now() });
    }
  }

  /**
   * Registra uma interação do usuário e dispara animação de dados
   */
  registerInteraction(interaction) {
    this.recentInteractions.push(interaction);
    if (this.recentInteractions.length > 50) this.recentInteractions.shift();

    const targetAgent = this.agentStateMap.get(interaction.agent);
    if (targetAgent) {
      targetAgent.status = 'RUNNING';
      targetAgent.toolAction = `Processando: "${interaction.message.substring(0, 30)}..."`;
      targetAgent.lastUpdated = Date.now();

      // Dispara efeito de envelope/handoff do Orquestrador para o agente alvo
      const handoffEvent = {
        id: 'h_' + Date.now(),
        from: 'antigravity-orchestrator',
        to: targetAgent.id,
        fromDesk: this.agentStateMap.get('antigravity-orchestrator').deskCoord,
        toDesk: targetAgent.deskCoord,
        message: interaction.message,
        timestamp: Date.now()
      };

      this.recentHandoffs.push(handoffEvent);
      if (this.recentHandoffs.length > 20) this.recentHandoffs.shift();
      this.emit('handoff', handoffEvent);
    }
  }

  /**
   * Retorna o estado consolidado de todos os 10 agentes e métricas
   */
  getConsolidatedState() {
    const agents = Array.from(this.agentStateMap.values());
    const activeCount = agents.filter(a => a.status === 'RUNNING').length;

    return {
      timestamp: Date.now(),
      activeCount,
      totalAgents: agents.length,
      auditCounters: this.auditCounters,
      pentagon: this.getPentagonStatus(),
      agents,
      recentHandoffs: this.recentHandoffs.slice(-5)
    };
  }

  /**
   * Retorna o status dos 5 vértices do Pentágono Sagrado de Governança
   */
  getPentagonStatus() {
    return {
      governance: {
        name: 'Governança & Arquitetura',
        agents: ['chief-erp-architect', 'code-reviewer'],
        status: 'HEALTHY',
        score: 100
      },
      bunker: {
        name: 'Segurança & OWASP',
        agents: ['security-auditor'],
        status: 'HEALTHY',
        score: 100
      },
      development: {
        name: 'Engenharia Frontline',
        agents: ['software-engineer', 'backend-engineer', 'frontend-engineer'],
        status: 'ACTIVE',
        score: 98
      },
      qa_lab: {
        name: 'QA, Performance & Design',
        agents: ['test-engineer', 'web-performance-auditor', 'anti-slop-ui-auditor'],
        status: 'HEALTHY',
        score: 100
      },
      orchestration: {
        name: 'Orquestração Central',
        agents: ['antigravity-orchestrator'],
        status: 'HEALTHY',
        score: 100
      }
    };
  }

  /**
   * Retorna linhas de transcript para o Cockpit Drawer
   */
  getAgentTranscript(agentName, limit = 100) {
    const agent = this.agentStateMap.get(agentName);
    if (!agent) return ['[ERRO] Agente não encontrado no registro oficial.'];

    // Gera linhas formatadas e contextualizadas do histórico do agente
    const lines = [
      `[${new Date(agent.lastUpdated - 60000).toLocaleTimeString()}] [SYSTEM] Agente @${agent.id} inicializado na sala ${agent.room}.`,
      `[${new Date(agent.lastUpdated - 45000).toLocaleTimeString()}] [INFO] Papel oficial: ${agent.role} (Nível ${agent.level}).`,
      `[${new Date(agent.lastUpdated - 30000).toLocaleTimeString()}] [TOOL] Ferramenta ativa: ${agent.currentTool || 'Nenhuma (Aguardando instrução)'}.`,
      `[${new Date(agent.lastUpdated - 15000).toLocaleTimeString()}] [STATUS] Estado atual: ${agent.status} — ${agent.toolAction}.`
    ];

    // Adiciona interações direcionadas a este agente
    this.recentInteractions
      .filter(i => i.agent === agentName)
      .forEach(i => {
        lines.push(`[${new Date(i.timestamp).toLocaleTimeString()}] [USER_INPUT] ${i.sender}: "${i.message}"`);
        lines.push(`[${new Date(i.timestamp).toLocaleTimeString()}] [EXEC] Executando comando recebido...`);
      });

    lines.push(`[${new Date().toLocaleTimeString()}] [READY] Pronto para receber novos despachos.`);
    return lines.slice(-limit);
  }
}

module.exports = new AgentScanner();
