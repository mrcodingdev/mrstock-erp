const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../vendas/pdv.php'), 'utf8');
const start = source.indexOf('let checkoutEmAndamento');
const code = source.slice(start < 0 ? source.indexOf('function abrirModalPagamento()') : start, source.indexOf('function atualizarClienteSelecionado('));
function setup(total = 20) {
    const elements = new Map();
    function element(id) {
        if (!elements.has(id)) elements.set(id, {
            value: '', disabled: false, style: {}, textContent: '', innerHTML: '',
            classList: { add() {}, remove() {}, toggle() {} }, focus() {}, select() {},
            options: [{ text: 'Consumidor Final' }], selectedIndex: 0,
        });
        return elements.get(id);
    }
    let submissions = 0;
    const callbacks = [];
    const requests = [];
    const destinations = [];
    const outcomes = [];
    element('modal_forma_pagamento').value = 'DINHEIRO';
    element('formVenda').submit = () => submissions++;
    const context = vm.createContext({
        document: { getElementById: element, querySelectorAll: () => [], querySelector: () => element('exato') },
        totalVendaAtual: total, carrinho: [{ id: 1, quantidade: 2 }], modalFinalizarInstancia: null,
        MRSTOCK_CONFIG: {}, recalcularTotal() {}, playBeep() {}, showToast() {}, window: { location: { assign: path => destinations.push(path) } },
        FormData: class { constructor() { this.fields = new Map(); } set(key, value) { this.fields.set(key, value); } },
        fetch: async (url, config) => {
            submissions++;
            requests.push(config);
            const outcome = outcomes.shift() || { status: 200, body: { status: 'ok', redirect: '/cupom' } };
            if (outcome instanceof Error) throw outcome;
            return { status: outcome.status, json: async () => outcome.body };
        },
        formatarMoeda: n => n.toLocaleString('pt-BR', { minimumFractionDigits: 2 }),
        bootstrap: { Modal: class { show() {} } }, setTimeout: fn => callbacks.push(fn),
    });
    vm.runInContext(code, context);
    return { context, element, callbacks, outcomes, requests, destinations, submissions: () => submissions };
}
const tests = [
    ['abertura em dinheiro exige valor explícito', () => {
        const s = setup(); s.context.abrirModalPagamento();
        assert.equal(s.element('modal_valor_recebido').value, '');
        assert.equal(s.element('btnConfirmarVendaModal').disabled, true);
        s.context.confirmarVendaFinal(); assert.equal(s.submissions(), 0);
    }],
    ['cédula de 50 calcula troco 30 e permite uma confirmação', () => {
        const s = setup(); s.context.abrirModalPagamento(); s.context.definirCedula(50);
        assert.match(s.element('trocoValorDisplay').textContent, /30,00/);
        assert.equal(s.element('btnConfirmarVendaModal').disabled, false);
        s.context.confirmarVendaFinal(); s.context.confirmarVendaFinal(); assert.equal(s.submissions(), 1);
    }],
    ['foco atrasado não sobrescreve cédula escolhida', () => {
        const s = setup(); s.context.abrirModalPagamento(); s.context.definirCedula(50);
        s.callbacks.forEach(fn => fn()); assert.equal(s.element('modal_valor_recebido').value, '50.00');
    }],
    ['Exato permanece disponível por escolha explícita', () => {
        const s = setup(); s.context.abrirModalPagamento(); s.context.definirValorExato();
        assert.equal(s.element('modal_valor_recebido').value, '20.00');
        assert.equal(s.element('btnConfirmarVendaModal').disabled, false);
        assert.match(s.element('trocoValorDisplay').textContent, /0,00/);
    }],
    ['valor insuficiente bloqueia finalização', () => {
        const s = setup(); s.context.abrirModalPagamento(); s.context.definirCedula(10);
        assert.equal(s.element('btnConfirmarVendaModal').disabled, true);
        s.context.confirmarVendaFinal(); assert.equal(s.submissions(), 0);
    }],
    ['reabrir modal limpa valor anterior', () => {
        const s = setup(); s.context.abrirModalPagamento(); s.context.definirCedula(50); s.context.abrirModalPagamento();
        assert.equal(s.element('modal_valor_recebido').value, '');
        assert.equal(s.element('btnConfirmarVendaModal').disabled, true);
    }],
    ['PIX e cartões não herdam bloqueio do dinheiro vazio', () => {
        for (const method of ['PIX', 'CARTÃO DE CRÉDITO', 'CARTÃO DE DÉBITO']) {
            const s = setup(); s.context.abrirModalPagamento(); s.element('modal_forma_pagamento').value = method;
            s.context.aoMudarFormaPagamentoModal(method);
            assert.equal(s.element('btnConfirmarVendaModal').disabled, false);
            s.context.confirmarVendaFinal(); assert.equal(s.submissions(), 1);
        }
    }],
    ['voltar de PIX para dinheiro exige nova informação', () => {
        const s = setup(); s.context.definirCedula(50); s.context.aoMudarFormaPagamentoModal('PIX');
        s.context.aoMudarFormaPagamentoModal('DINHEIRO');
        assert.equal(s.element('modal_valor_recebido').value, '');
        assert.equal(s.element('btnConfirmarVendaModal').disabled, true);
    }],
    ['vazio ou inválido não confirma nem em total zero', () => {
        for (const value of ['', ' ', 'abc', 'Infinity', '-1']) {
            const s = setup(0); s.element('modal_valor_recebido').value = value; s.context.calcularTroco();
            assert.equal(s.element('btnConfirmarVendaModal').disabled, true, value);
        }
    }],
    ['aviso preserva carrinho e envia token numa segunda confirmação', async () => {
        const s = setup();
        s.context.abrirModalPagamento();
        s.context.definirCedula(50);
        s.outcomes.push({ status: 409, body: { status: 'margem_aviso', token: 'token-da-fixture' } });
        await s.context.confirmarVendaFinal();
        assert.match(s.element('pdvFeedbackCheckout').textContent, /margem negativa/i);
        assert.equal(s.requests[0].body.fields.has('confirmacao_margem'), false);
        assert.equal(s.destinations.length, 0);
        assert.equal(s.context.carrinho[0].quantidade, 2);
        await s.context.confirmarVendaFinal();
        assert.equal(s.requests[1].body.fields.get('confirmacao_margem'), 'token-da-fixture');
        assert.deepEqual(s.destinations, ['/cupom']);
    }],
    ['falha de rede oferece reenvio explícito com a chave e o aceite originais', async () => {
        const s = setup();
        s.context.abrirModalPagamento();
        s.context.definirCedula(50);
        s.outcomes.push({ status: 409, body: { status: 'margem_aviso', token: 'token-da-fixture' } }, new Error('rede indisponível'));
        await s.context.confirmarVendaFinal();
        await s.context.confirmarVendaFinal();
        assert.equal(s.submissions(), 2);
        assert.equal(s.requests[1].body.fields.get('confirmacao_margem'), 'token-da-fixture');
        assert.equal(s.context.carrinho[0].quantidade, 2);
        assert.equal(s.element('btnConfirmarVendaModal').textContent, 'Reenviar esta venda');
        assert.equal(s.element('modal_forma_pagamento').disabled, true);
        assert.equal(s.element('modal_valor_recebido').disabled, true);
        assert.equal(s.element('btnConfirmarVendaModal').disabled, false);
        s.outcomes.push({ status: 409, body: { status: 'margem_ja_confirmada', redirect: '/cupom' } });
        await s.context.confirmarVendaFinal();
        assert.strictEqual(s.requests[2].body, s.requests[1].body);
        assert.deepEqual(s.destinations, ['/cupom']);
    }],
    ['sessão recusada mantém finalização bloqueada e libera os controles do modal', async () => {
        const s = setup();
        s.context.abrirModalPagamento();
        s.context.definirCedula(50);
        s.outcomes.push({ status: 403 });
        await s.context.confirmarVendaFinal();
        assert.equal(s.element('modal_forma_pagamento').disabled, false);
        assert.equal(s.element('modal_valor_recebido').disabled, false);
        assert.equal(s.element('btnConfirmarVendaModal').disabled, true);
        assert.match(s.element('pdvFeedbackCheckout').textContent, /sessão inválida/i);
    }],
];
async function run() {
    let failed = 0;
    for (const [name, test] of tests) {
        try { await test(); console.log('PASSOU: ' + name); }
        catch (e) { failed++; console.log('FALHOU: ' + name + ' — ' + e.message); }
    }
    console.log(`${tests.length - failed}/${tests.length} cenários aprovados`);
    process.exitCode = failed ? 1 : 0;
}
run();

