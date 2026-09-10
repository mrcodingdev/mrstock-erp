/**
 * ANTIGRAVITY OFFICE 2D PIXEL ART — HTTP + SSE SERVER
 * Servidor Express na porta 4444 (fallback 4445)
 * Fornece SSE em tempo real, APIs de status, transcripts e interações.
 */

const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const scanner = require('./agent-scanner');

const app = express();
const PRIMARY_PORT = 4444;
const FALLBACK_PORT = 4445;

app.use(cors());
app.use(express.json());
app.use(express.static(path.join(__dirname, 'public')));

// Clientes SSE conectados
let sseClients = [];

/**
 * Endpoint SSE: Stream em tempo real a cada 500ms
 */
app.get('/api/stream', (req, res) => {
  res.setHeader('Content-Type', 'text/event-stream');
  res.setHeader('Cache-Control', 'no-cache');
  res.setHeader('Connection', 'keep-alive');
  res.setHeader('X-Accel-Buffering', 'no');
  res.flushHeaders();

  const clientId = Date.now() + Math.random().toString(36).substring(2, 7);
  const newClient = { id: clientId, res };
  sseClients.push(newClient);

  // Enviar estado inicial imediatamente
  const initialState = scanner.getConsolidatedState();
  res.write(`event: state\ndata: ${JSON.stringify(initialState)}\n\n`);

  req.on('close', () => {
    sseClients = sseClients.filter(c => c.id !== clientId);
  });
});

/**
 * Endpoint JSON: Status Consolidado
 */
app.get('/api/status', (req, res) => {
  try {
    const state = scanner.getConsolidatedState();
    res.json({
      success: true,
      timestamp: new Date().toISOString(),
      data: state
    });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

/**
 * Endpoint JSON: Transcript das últimas 100 linhas do agente
 */
app.get('/api/agent/:name/transcript', (req, res) => {
  try {
    const agentName = req.params.name;
    const transcript = scanner.getAgentTranscript(agentName, 100);
    res.json({
      success: true,
      agent: agentName,
      lines: transcript
    });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

/**
 * Endpoint POST: Interagir com o agente (enviar mensagem/comando)
 */
app.post('/api/agent/:name/interact', (req, res) => {
  try {
    const agentName = req.params.name;
    const { message, sender = 'User' } = req.body;

    if (!message || typeof message !== 'string') {
      return res.status(400).json({ success: false, error: 'Mensagem inválida ou vazia.' });
    }

    const interactionRecord = {
      timestamp: new Date().toISOString(),
      agent: agentName,
      sender,
      message: message.trim()
    };

    // Grava no histórico local de interações
    const interactionsPath = path.join(__dirname, 'interactions.jsonl');
    fs.appendFileSync(interactionsPath, JSON.stringify(interactionRecord) + '\n', 'utf8');

    // Notifica scanner para disparar evento visual
    scanner.registerInteraction(interactionRecord);

    res.json({
      success: true,
      message: `Instrução transmitida com sucesso para @${agentName}.`,
      record: interactionRecord
    });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

/**
 * Endpoint JSON: Saúde do Pentágono Sagrado
 */
app.get('/api/system/pentagon', (req, res) => {
  try {
    const pentagon = scanner.getPentagonStatus();
    res.json({
      success: true,
      pentagon
    });
  } catch (err) {
    res.status(500).json({ success: false, error: err.message });
  }
});

// Broadcast periódico SSE a cada 500ms
setInterval(() => {
  if (sseClients.length === 0) return;
  const state = scanner.getConsolidatedState();
  const payload = `event: state\ndata: ${JSON.stringify(state)}\n\n`;
  sseClients.forEach(client => {
    try {
      client.res.write(payload);
    } catch (e) {
      // Ignora erro de conexão individual
    }
  });
}, 500);

// Transmissão de eventos especiais (Handoff e Auditoria)
scanner.on('handoff', (handoffData) => {
  const payload = `event: handoff\ndata: ${JSON.stringify(handoffData)}\n\n`;
  sseClients.forEach(client => client.res.write(payload));
});

scanner.on('audit', (auditData) => {
  const payload = `event: audit\ndata: ${JSON.stringify(auditData)}\n\n`;
  sseClients.forEach(client => client.res.write(payload));
});

// Inicia servidor com fallback de porta
function startServer(port) {
  const server = app.listen(port, () => {
    console.log(`\n=============================================================`);
    console.log(`🏢 ANTIGRAVITY OFFICE 2D PIXEL ART — SERVER RUNNING`);
    console.log(`📡 URL Principal: http://localhost:${port}`);
    console.log(`📊 SSE Stream:    http://localhost:${port}/api/stream`);
    console.log(`🛡️ MrStock ERP:   Papelaria Real Governança Integrada`);
    console.log(`=============================================================\n`);
  });

  server.on('error', (err) => {
    if (err.code === 'EADDRINUSE' && port === PRIMARY_PORT) {
      console.warn(`[WARN] Porta ${PRIMARY_PORT} ocupada. Tentando fallback para ${FALLBACK_PORT}...`);
      startServer(FALLBACK_PORT);
    } else {
      console.error(`[ERROR] Falha ao iniciar servidor:`, err);
    }
  });
}

// Inicia escaneamento e servidor
scanner.init();
startServer(PRIMARY_PORT);
