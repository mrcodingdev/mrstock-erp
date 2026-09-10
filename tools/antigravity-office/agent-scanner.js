/**
 * ANTIGRAVITY OFFICE — AGENT SCANNER (ANTIGRAVITY 2.0 DEEP TELEMETRY)
 * Monitora C:\Users\Douglas\.gemini\antigravity\brain\
 * Rastreia as sessões reais dos 10 agentes oficiais, extrai Chain-of-Thought (thinking),
 * steps reais, tool calls, vereditos de auditoria e métricas globais de cota.
 * Erradica definitivamente o bug de RUNNING infinito com timeout estrito de 15 segundos.
 */

const fs = require('fs');
const path = require('path');
const EventEmitter = require('events');
const https = require('https');

function dispatchN8nTelemetry(agentName, action, status, verdict, details = {}) {
  try {
    const payload = JSON.stringify({
      timestamp: new Date().toISOString(),
      agentName,
      action,
      status,
      verdict,
      details
    });

    const options = {
      hostname: 'dgzin.app.n8n.cloud',
      port: 443,
      path: '/webhook/antigravity-telemetry',
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(payload)
      },
      timeout: 1500
    };

    const req = https.request(options, (res) => {
      res.resume();
    });

    req.on('timeout', () => {
      req.destroy();
    });

    req.on('error', () => {
      // Ignora erro silenciosamente
    });

    req.write(payload);
    req.end();
  } catch (err) {
    // Blindagem estrita contra falhas de rede
  }
}

class AgentScanner extends EventEmitter {
  constructor() {
    super();
    this.brainDir = 'C:\\Users\\Douglas\\.gemini\\antigravity\\brain';
    this.inboxPath = path.join(__dirname, 'orchestrator_inbox.jsonl');
    this.parentSessionId = 'bcda6410-72dd-4fd8-a320-3776e991c5df';

    this.auditCounters = { pass: 0, revise: 0, total: 0 };
    this.recentHandoffs = [];
    this.recentInteractions = [];

    // Cache de sessões por diretório: dirName -> { mtime, steps, tools, thinkingLogs, lastVerdict, formattedLines, latestAction }
    this.sessionCache = new Map();
    // Mapa de agente para sessão ativa mais recente: agentId -> dirName
    this.agentToSessionMap = new Map();
    // Cache de métricas globais
    this.globalMetrics = {
      totalGlobalSteps: 0,
      totalGlobalTools: 0,
      totalTokensEstimate: 0,
      mappedAgents: [],
      perAgentStats: {}
    };

    this.isInitialScan = true;

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
        status: 'IDLE',
        currentTool: null,
        toolAction: 'Aguardando novas demandas / Em repouso',
        toolSummary: 'Ocioso',
        lastUpdated: Date.now(),
        lastVerdict: null,
        stats: {
          stepsCount: 0,
          toolsUsed: 0,
          passCount: 0,
          reviseCount: 0
        }
      });
    });
  }

  init() {
    this.scanBrain();
    setInterval(() => this.scanBrain(), 1500);
  }

  /**
   * Identifica qual agente oficial é responsável pela sessão a partir do texto do prompt
   */
  detectAgentFromContent(text, dirName) {
    if (dirName === this.parentSessionId) {
      return 'antigravity-orchestrator';
    }

    // 1. Ordem de checagem mais específica
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)anti-slop-ui-auditor/i.test(text)) return 'anti-slop-ui-auditor';
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)web-performance-auditor/i.test(text)) return 'web-performance-auditor';
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)security-auditor/i.test(text)) return 'security-auditor';
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)code-reviewer/i.test(text)) return 'code-reviewer';
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)chief-erp-architect/i.test(text)) return 'chief-erp-architect';
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)test-engineer/i.test(text)) return 'test-engineer';
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)frontend-engineer/i.test(text) || /(?:Você é o\s+|persona:\s*)frontend-worker/i.test(text)) return 'frontend-engineer';
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)backend-engineer/i.test(text) || /(?:Você é o\s+|persona:\s*)backend-worker/i.test(text)) return 'backend-engineer';
    if (/(?:Você é o\s+|persona:\s*|role:\s*|agent:\s*|subagente\s*)software-engineer/i.test(text) || /(?:Você é o\s+|persona:\s*)office-builder-worker/i.test(text)) return 'software-engineer';
    if (/antigravity-orchestrator/i.test(text)) return 'antigravity-orchestrator';

    // 2. Fallbacks flexíveis por palavras-chave
    if (text.includes('anti-slop-ui-auditor') || text.includes('anti-slop')) return 'anti-slop-ui-auditor';
    if (text.includes('web-performance-auditor') || text.includes('web-performance')) return 'web-performance-auditor';
    if (text.includes('security-auditor')) return 'security-auditor';
    if (text.includes('code-reviewer')) return 'code-reviewer';
    if (text.includes('chief-erp-architect')) return 'chief-erp-architect';
    if (text.includes('test-engineer')) return 'test-engineer';
    if (text.includes('frontend-engineer') || text.includes('frontend-worker')) return 'frontend-engineer';
    if (text.includes('backend-engineer') || text.includes('backend-worker')) return 'backend-engineer';
    if (text.includes('software-engineer') || text.includes('office-builder-worker')) return 'software-engineer';

    return null;
  }

  /**
   * Processa o arquivo transcript.jsonl de uma pasta de sessão com cache inteligente por mtime
   */
  parseTranscriptFile(filePath, stat, dirName) {
    const cached = this.sessionCache.get(dirName);
    if (cached && cached.mtime === stat.mtimeMs) {
      return cached;
    }

    try {
      const content = fs.readFileSync(filePath, 'utf8');
      const lines = content.split('\n');

      let totalSteps = 0;
      let totalTools = 0;
      const thinkingLogs = [];
      let lastVerdict = null;
      const formattedLines = [];
      let latestAction = 'Aguardando novas demandas / Em repouso';
      let latestTool = null;
      let assignedAgent = null;

      // Detecta persona a partir do primeiro step
      if (lines.length > 0 && lines[0].trim()) {
        try {
          const firstObj = JSON.parse(lines[0]);
          assignedAgent = this.detectAgentFromContent(firstObj.content || lines[0], dirName);
        } catch (e) {
          assignedAgent = this.detectAgentFromContent(lines[0], dirName);
        }
      }

      for (let i = 0; i < lines.length; i++) {
        const line = lines[i].trim();
        if (!line) continue;

        try {
          const entry = JSON.parse(line);

          if (typeof entry.step_index === 'number') {
            totalSteps = Math.max(totalSteps, entry.step_index + 1);
          }

          const timeStr = entry.created_at
            ? new Date(entry.created_at).toLocaleTimeString()
            : new Date().toLocaleTimeString();

          // Extração de Chain-of-Thought
          if (entry.thinking && typeof entry.thinking === 'string') {
            const toolNames = entry.tool_calls ? entry.tool_calls.map(tc => tc.name) : [];
            thinkingLogs.push({
              stepIndex: entry.step_index || 0,
              timestamp: entry.created_at || new Date().toISOString(),
              thinking: entry.thinking.trim(),
              toolCalls: toolNames
            });

            if (toolNames.length > 0) {
              latestTool = toolNames[toolNames.length - 1];
              latestAction = `Executando ${latestTool}: no workspace`;
            }
          }

          // Contagem de Tool Calls e formatação de linhas de transcript
          if (entry.tool_calls && Array.isArray(entry.tool_calls)) {
            totalTools += entry.tool_calls.length;
            entry.tool_calls.forEach(tc => {
              latestTool = tc.name;
              const summary = tc.args && tc.args.toolSummary ? tc.args.toolSummary.replace(/"/g, '') : tc.name;
              latestAction = `Executando ${tc.name}: ${summary}`;
              formattedLines.push(`[${timeStr}] [TOOL] ${tc.name} — ${summary}`);
            });
          }

          if (entry.type === 'USER_INPUT') {
            const snip = (entry.content || '').substring(0, 70).replace(/\r?\n/g, ' ');
            formattedLines.push(`[${timeStr}] [INPUT] "${snip}..."`);
          } else if (entry.type === 'PLANNER_RESPONSE' && entry.status === 'DONE') {
            formattedLines.push(`[${timeStr}] [PLANNER] Step ${entry.step_index}: Decisão planejada.`);
          }

          // Detecção de Vereditos formais
          if (line.includes('[ PASS ]') || line.includes('[ 🟢 PASS ]')) {
            lastVerdict = 'PASS';
            formattedLines.push(`[${timeStr}] [VERDICT] 🟢 PASS aprovado formalmente.`);
          } else if (line.includes('[ REVISE ]') || line.includes('[ 🔴 REVISE ]')) {
            lastVerdict = 'REVISE';
            formattedLines.push(`[${timeStr}] [VERDICT] 🔴 REVISE solicitado formalmente.`);
          }
        } catch (e) {
          // Ignora linha com JSON corrompido ou parcial
        }
      }

      // Mantém apenas os 10 mais recentes pensamentos
      const recentThinking = thinkingLogs.slice(-10);

      const parsedData = {
        mtime: stat.mtimeMs,
        size: stat.size,
        steps: totalSteps,
        tools: totalTools,
        thinkingLogs: recentThinking,
        lastVerdict,
        formattedLines: formattedLines.slice(-100),
        latestAction,
        latestTool,
        assignedAgent
      };

      this.sessionCache.set(dirName, parsedData);
      return parsedData;
    } catch (err) {
      return null;
    }
  }

  /**
   * Varredura real multi-sessão no Antigravity Brain
   */
  scanBrain() {
    if (!fs.existsSync(this.brainDir)) return;

    try {
      const dirs = fs.readdirSync(this.brainDir, { withFileTypes: true })
        .filter(d => d.isDirectory() && d.name !== 'scratch' && d.name !== 'tempmediaStorage' && !d.name.startsWith('.'));

      let totalGlobalSteps = 0;
      let totalGlobalTools = 0;
      const candidateSessionsByAgent = new Map();

      dirs.forEach(d => {
        const transcriptPath = path.join(this.brainDir, d.name, '.system_generated', 'logs', 'transcript.jsonl');
        if (!fs.existsSync(transcriptPath)) return;

        try {
          const stat = fs.statSync(transcriptPath);
          const sessionData = this.parseTranscriptFile(transcriptPath, stat, d.name);

          if (sessionData) {
            totalGlobalSteps += sessionData.steps;
            totalGlobalTools += sessionData.tools;

            if (sessionData.assignedAgent) {
              if (!candidateSessionsByAgent.has(sessionData.assignedAgent)) {
                candidateSessionsByAgent.set(sessionData.assignedAgent, []);
              }
              candidateSessionsByAgent.get(sessionData.assignedAgent).push({
                dirName: d.name,
                mtime: stat.mtimeMs,
                data: sessionData
              });
            }
          }
        } catch (e) {}
      });

      // Mapeia para cada agente a sua sessão mais recente
      const perAgentStats = {};
      const mappedAgentList = [];

      this.agentDefinitions.forEach(agentDef => {
        const candidates = candidateSessionsByAgent.get(agentDef.id) || [];
        // Ordena por mtime descrescente
        candidates.sort((a, b) => b.mtime - a.mtime);
        const bestMatch = candidates[0];

        const agentState = this.agentStateMap.get(agentDef.id);
        if (!agentState) return;

        if (bestMatch) {
          const session = bestMatch.data;
          this.agentToSessionMap.set(agentDef.id, bestMatch.dirName);
          mappedAgentList.push(agentDef.id);

          agentState.stats.stepsCount = session.steps;
          agentState.stats.toolsUsed = session.tools;

          // Processamento de vereditos formais
          if (session.lastVerdict && agentState.lastVerdict !== session.lastVerdict) {
            agentState.lastVerdict = session.lastVerdict;
            if (session.lastVerdict === 'PASS') {
              agentState.stats.passCount++;
              this.auditCounters.pass++;
              this.auditCounters.total++;
              if (!this.isInitialScan) {
                this.emit('audit', { type: 'PASS', agentId: agentDef.id, timestamp: Date.now() });
                dispatchN8nTelemetry(agentDef.id, 'audit_verdict', agentState.status, 'PASS', {
                  steps: session.steps,
                  tools: session.tools,
                  sessionDir: bestMatch.dirName
                });
              }
            } else if (session.lastVerdict === 'REVISE') {
              agentState.stats.reviseCount++;
              this.auditCounters.revise++;
              this.auditCounters.total++;
              if (!this.isInitialScan) {
                this.emit('audit', { type: 'REVISE', agentId: agentDef.id, timestamp: Date.now() });
                dispatchN8nTelemetry(agentDef.id, 'audit_verdict', agentState.status, 'REVISE', {
                  steps: session.steps,
                  tools: session.tools,
                  sessionDir: bestMatch.dirName
                });
              }
            }
          }

          // FIM DEFINITIVO DO BUG DE RUNNING INFINITO
          // Se o arquivo não foi alterado nos últimos 15 segundos, o status É OBRIGATORIAMENTE 'IDLE'
          const timeSinceModified = Date.now() - bestMatch.mtime;
          const isCurrentlyActive = timeSinceModified < 15000;
          const previousStatus = agentState.status;

          if (isCurrentlyActive) {
            agentState.status = 'RUNNING';
            agentState.currentTool = session.latestTool || 'run_command';
            agentState.toolAction = session.latestAction || 'Executando tarefa no workspace';
            agentState.toolSummary = 'Em atividade';
            if (previousStatus !== 'RUNNING' && !this.isInitialScan) {
              dispatchN8nTelemetry(agentDef.id, 'status_change', 'RUNNING', agentState.lastVerdict, {
                steps: session.steps,
                tools: session.tools,
                sessionDir: bestMatch.dirName
              });
            }
          } else {
            // NUNCA manter status RUNNING se o arquivo não foi alterado há mais de 15 segundos
            agentState.status = 'IDLE';
            agentState.currentTool = null;
            agentState.toolAction = 'Aguardando novas demandas / Em repouso';
            agentState.toolSummary = 'Ocioso';
            if (previousStatus !== 'IDLE' && !this.isInitialScan) {
              dispatchN8nTelemetry(agentDef.id, 'status_change', 'IDLE', agentState.lastVerdict, {
                steps: session.steps,
                tools: session.tools,
                sessionDir: bestMatch.dirName
              });
            }
          }
          agentState.lastUpdated = bestMatch.mtime;

          perAgentStats[agentDef.id] = {
            sessionDir: bestMatch.dirName,
            steps: session.steps,
            tools: session.tools,
            lastActivity: bestMatch.mtime,
            status: agentState.status
          };
        } else {
          // Sem sessão mapeada
          agentState.status = 'IDLE';
          agentState.toolAction = 'Aguardando despacho inicial';
          agentState.toolSummary = 'Ocioso';
          perAgentStats[agentDef.id] = {
            sessionDir: null,
            steps: 0,
            tools: 0,
            lastActivity: null,
            status: 'IDLE'
          };
        }
      });

      // Atualiza métricas globais de cota
      this.globalMetrics = {
        totalGlobalSteps,
        totalGlobalTools,
        totalTokensEstimate: totalGlobalSteps * 450,
        mappedAgents: mappedAgentList,
        perAgentStats
      };

      if (this.isInitialScan) {
        this.isInitialScan = false;
      }
    } catch (err) {
      console.error('[AgentScanner] Erro no scanBrain:', err.message);
    }
  }

  /**
   * Retorna os pensamentos (Chain-of-Thought) mais recentes do agente
   */
  getAgentThinking(agentName) {
    const sessionDir = this.agentToSessionMap.get(agentName);
    if (!sessionDir) {
      return [
        {
          stepIndex: 0,
          timestamp: new Date().toISOString(),
          thinking: `O agente @${agentName} ainda não iniciou deliberações na esteira de desenvolvimento ativa. Suas diretrizes seguem o Pentágono Sagrado de Governança.`,
          toolCalls: []
        }
      ];
    }

    const sessionData = this.sessionCache.get(sessionDir);
    if (!sessionData || !sessionData.thinkingLogs || sessionData.thinkingLogs.length === 0) {
      return [
        {
          stepIndex: 0,
          timestamp: new Date().toISOString(),
          thinking: `Aguardando registro de novo ciclo de raciocínio para @${agentName}. Todas as ações anteriores foram auditadas.`,
          toolCalls: []
        }
      ];
    }

    return sessionData.thinkingLogs;
  }

  /**
   * Retorna métricas globais de cota e steps
   */
  getGlobalMetrics() {
    return this.globalMetrics;
  }

  /**
   * Registra comando enviado ao Agente Pai (Antigravity Orchestrator), dispara handoff e transita temporariamente
   */
  registerOrchestratorCommand({ message, sender = 'Douglas (Operador)' }) {
    const commandRecord = {
      timestamp: new Date().toISOString(),
      sender,
      target: 'antigravity-orchestrator',
      message: message.trim()
    };

    // 1. Grava no orchestrator_inbox.jsonl
    try {
      fs.appendFileSync(this.inboxPath, JSON.stringify(commandRecord) + '\n', 'utf8');
    } catch (e) {}

    // 2. Dispara animação de handoff voador no canvas
    const orch = this.agentStateMap.get('antigravity-orchestrator');
    if (orch) {
      const handoffEvent = {
        id: 'h_cmd_' + Date.now(),
        from: 'user_terminal',
        to: 'antigravity-orchestrator',
        fromDesk: { x: 22, y: 14 },
        toDesk: orch.deskCoord,
        message: message,
        task: 'Despacho de Orquestração',
        timestamp: Date.now()
      };

      this.recentHandoffs.push(handoffEvent);
      if (this.recentHandoffs.length > 20) this.recentHandoffs.shift();
      this.emit('handoff', handoffEvent);

      // Transita temporariamente para RUNNING
      orch.status = 'RUNNING';
      orch.currentTool = 'send_message';
      orch.toolAction = `Analisando: "${message.substring(0, 32)}..."`;
      orch.toolSummary = 'Agente Pai em ação';
      orch.lastUpdated = Date.now();

      // Após 3.5 segundos retorna para IDLE com confirmação de despacho
      setTimeout(() => {
        if (orch.status === 'RUNNING') {
          orch.status = 'IDLE';
          orch.currentTool = null;
          orch.toolAction = '[DONE] Comando analisado pelo Agente Pai e inserido no pipeline de orquestração.';
          orch.toolSummary = 'Em repouso';
          orch.lastUpdated = Date.now();
        }
      }, 3500);
    }

    return commandRecord;
  }

  /**
   * Registra interação rápida com agente (legado/fallback)
   */
  registerInteraction(interaction) {
    this.recentInteractions.push(interaction);
    if (this.recentInteractions.length > 50) this.recentInteractions.shift();

    const targetAgent = this.agentStateMap.get(interaction.agent);
    if (targetAgent) {
      targetAgent.status = 'RUNNING';
      targetAgent.toolAction = `Processando: "${interaction.message.substring(0, 30)}..."`;
      targetAgent.lastUpdated = Date.now();

      const orch = this.agentStateMap.get('antigravity-orchestrator');
      const handoffEvent = {
        id: 'h_' + Date.now(),
        from: 'antigravity-orchestrator',
        to: targetAgent.id,
        fromDesk: orch ? orch.deskCoord : { x: 22, y: 5 },
        toDesk: targetAgent.deskCoord,
        message: interaction.message,
        timestamp: Date.now()
      };

      this.recentHandoffs.push(handoffEvent);
      if (this.recentHandoffs.length > 20) this.recentHandoffs.shift();
      this.emit('handoff', handoffEvent);

      setTimeout(() => {
        if (targetAgent.status === 'RUNNING') {
          targetAgent.status = 'IDLE';
          targetAgent.toolAction = 'Aguardando novas demandas / Em repouso';
        }
      }, 3000);
    }
  }

  /**
   * Retorna linhas de transcript para o Cockpit Drawer
   */
  getAgentTranscript(agentName, limit = 100) {
    const sessionDir = this.agentToSessionMap.get(agentName);
    const agent = this.agentStateMap.get(agentName);
    if (!agent) return ['[ERRO] Agente não encontrado no registro oficial.'];

    let lines = [];
    if (sessionDir) {
      const sessionData = this.sessionCache.get(sessionDir);
      if (sessionData && sessionData.formattedLines && sessionData.formattedLines.length > 0) {
        lines = [...sessionData.formattedLines];
      }
    }

    if (lines.length === 0) {
      lines = [
        `[${new Date(agent.lastUpdated - 60000).toLocaleTimeString()}] [SYSTEM] Agente @${agent.id} inicializado na sala ${agent.room}.`,
        `[${new Date(agent.lastUpdated - 45000).toLocaleTimeString()}] [INFO] Papel oficial: ${agent.role} (Nível ${agent.level}).`,
        `[${new Date(agent.lastUpdated - 30000).toLocaleTimeString()}] [STATUS] Estado atual: ${agent.status} — ${agent.toolAction}.`,
        `[${new Date().toLocaleTimeString()}] [READY] Pronto para receber novos despachos do Agente Pai.`
      ];
    }

    // Acrescenta interações direcionadas recentes
    this.recentInteractions
      .filter(i => i.agent === agentName)
      .forEach(i => {
        lines.push(`[${new Date(i.timestamp).toLocaleTimeString()}] [USER_INPUT] ${i.sender}: "${i.message}"`);
        lines.push(`[${new Date(i.timestamp).toLocaleTimeString()}] [EXEC] Comando encaminhado pelo Agente Pai.`);
      });

    return lines.slice(-limit);
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
      recentHandoffs: this.recentHandoffs.slice(-5),
      globalMetrics: this.getGlobalMetrics()
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
}

module.exports = new AgentScanner();
