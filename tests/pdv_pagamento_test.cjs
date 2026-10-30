const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');
const source = fs.readFileSync(path.join(__dirname, '../vendas/pdv.php'), 'utf8');
const code = source.slice(source.indexOf('function abrirModalPagamento()'), source.indexOf('function atualizarClienteSelecionado('));
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
    element('modal_forma_pagamento').value = 'DINHEIRO';
    element('formVenda').submit = () => submissions++;
    const context = vm.createContext({
        document: { getElementById: element, querySelectorAll: () => [], querySelector: () => element('exato') },
        totalVendaAtual: total, carrinho: [{ id: 1, quantidade: 2 }], modalFinalizarInstancia: null,
        MRSTOCK_CONFIG: {}, recalcularTotal() {}, playBeep() {}, showToast() {},
        formatarMoeda: n => n.toLocaleString('pt-BR', { minimumFractionDigits: 2 }),
        bootstrap: { Modal: class { show() {} } }, setTimeout: fn => callbacks.push(fn),
    });
    vm.runInContext(code, context);
    return { context, element, callbacks, submissions: () => submissions };
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
];
let failed = 0;
for (const [name, test] of tests) {
    try { test(); console.log('PASSOU: ' + name); }
    catch (e) { failed++; console.log('FALHOU: ' + name + ' — ' + e.message); }
}
console.log(`${tests.length - failed}/${tests.length} cenários aprovados`);
process.exitCode = failed ? 1 : 0;

