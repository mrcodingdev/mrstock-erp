/**
 * ANTIGRAVITY OFFICE — 8-BIT AUDIO SYNTHESIZER
 * Sintetizador nativo Web Audio API (Zero dependências externas)
 */

class AudioFx {
  constructor() {
    this.ctx = null;
    this.isMuted = localStorage.getItem('antigravity_office_muted') === 'true';
    this.masterGain = null;
  }

  init() {
    if (this.ctx) return;
    try {
      const AudioContext = window.AudioContext || window.webkitAudioContext;
      this.ctx = new AudioContext();
      this.masterGain = this.ctx.createGain();
      this.masterGain.gain.setValueAtTime(this.isMuted ? 0 : 0.15, this.ctx.currentTime);
      this.masterGain.connect(this.ctx.destination);
    } catch (e) {
      console.warn('[AudioFx] Web Audio API não suportada.');
    }
  }

  ensureContext() {
    if (!this.ctx) this.init();
    if (this.ctx && this.ctx.state === 'suspended') {
      this.ctx.resume();
    }
  }

  toggleMute() {
    this.isMuted = !this.isMuted;
    try {
      localStorage.setItem('antigravity_office_muted', this.isMuted ? 'true' : 'false');
    } catch (e) {}
    if (this.masterGain && this.ctx) {
      this.masterGain.gain.setValueAtTime(this.isMuted ? 0 : 0.15, this.ctx.currentTime);
    }
    return this.isMuted;
  }

  /**
   * Clique suave de tecla mecânica
   */
  playKeystroke() {
    if (this.isMuted) return;
    this.ensureContext();
    if (!this.ctx) return;

    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();

    osc.type = 'triangle';
    osc.frequency.setValueAtTime(180 + Math.random() * 40, this.ctx.currentTime);
    osc.frequency.exponentialRampToValueAtTime(40, this.ctx.currentTime + 0.04);

    gain.gain.setValueAtTime(0.08, this.ctx.currentTime);
    gain.gain.exponentialRampToValueAtTime(0.001, this.ctx.currentTime + 0.04);

    osc.connect(gain);
    gain.connect(this.masterGain);

    osc.start();
    osc.stop(this.ctx.currentTime + 0.04);
  }

  /**
   * Beep duplo ao iniciar ferramenta
   */
  playToolBeep() {
    if (this.isMuted) return;
    this.ensureContext();
    if (!this.ctx) return;

    const now = this.ctx.currentTime;
    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();

    osc.type = 'square';
    osc.frequency.setValueAtTime(587.33, now); // D5
    osc.frequency.setValueAtTime(880.00, now + 0.05); // A5

    gain.gain.setValueAtTime(0.06, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.12);

    osc.connect(gain);
    gain.connect(this.masterGain);

    osc.start();
    osc.stop(now + 0.12);
  }

  /**
   * Fanfarra retrô de vitória 8-bit (Level Up / PASS)
   */
  playPassFanfare() {
    if (this.isMuted) return;
    this.ensureContext();
    if (!this.ctx) return;

    const notes = [523.25, 659.25, 783.99, 1046.50]; // C5, E5, G5, C6
    const now = this.ctx.currentTime;

    notes.forEach((freq, idx) => {
      const osc = this.ctx.createOscillator();
      const gain = this.ctx.createGain();

      osc.type = 'square';
      osc.frequency.setValueAtTime(freq, now + idx * 0.08);

      gain.gain.setValueAtTime(0.1, now + idx * 0.08);
      gain.gain.exponentialRampToValueAtTime(0.001, now + idx * 0.08 + 0.18);

      osc.connect(gain);
      gain.connect(this.masterGain);

      osc.start(now + idx * 0.08);
      osc.stop(now + idx * 0.08 + 0.18);
    });
  }

  /**
   * Alerta suave de advertência técnica (REVISE)
   */
  playReviseAlert() {
    if (this.isMuted) return;
    this.ensureContext();
    if (!this.ctx) return;

    const now = this.ctx.currentTime;
    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();

    osc.type = 'sawtooth';
    osc.frequency.setValueAtTime(440, now);
    osc.frequency.exponentialRampToValueAtTime(220, now + 0.25);

    gain.gain.setValueAtTime(0.08, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.25);

    osc.connect(gain);
    gain.connect(this.masterGain);

    osc.start();
    osc.stop(now + 0.25);
  }

  /**
   * Feixe de dados de Handoff (envelope voando)
   */
  playHandoffWhoosh() {
    if (this.isMuted) return;
    this.ensureContext();
    if (!this.ctx) return;

    const now = this.ctx.currentTime;
    const osc = this.ctx.createOscillator();
    const gain = this.ctx.createGain();

    osc.type = 'sine';
    osc.frequency.setValueAtTime(300, now);
    osc.frequency.exponentialRampToValueAtTime(1200, now + 0.18);

    gain.gain.setValueAtTime(0.07, now);
    gain.gain.exponentialRampToValueAtTime(0.001, now + 0.2);

    osc.connect(gain);
    gain.connect(this.masterGain);

    osc.start();
    osc.stop(now + 0.2);
  }
}

window.audioFx = new AudioFx();
