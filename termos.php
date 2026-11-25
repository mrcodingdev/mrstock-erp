<?php
require_once __DIR__ . '/config.php';

$documentoLegal = 'termos';
$pageTitle = 'Termos de Uso';
$descricaoLegal = 'Condições de acesso e uso do MrStock ERP, projeto acadêmico da equipe Mr. Coding.';
$metaDescricaoLegal = 'Termos de Uso do MrStock ERP.';
$secoesLegais = [
    'Sobre o Projeto e o Acesso',
    'Perfis de Acesso e Contas de Usuário (RBAC)',
    'Operação e Recursos do Sistema',
    'Emissão de Comprovantes de Venda',
    'Disponibilidade do Ambiente',
    'Propriedade Intelectual e Direitos Autorais',
    'Canais de Suporte e Atendimento',
];

require __DIR__ . '/inc/documento_legal_inicio.php';
?>

<section id="termos-1" class="legal-section" tabindex="-1">
    <h2>
        1. Sobre o Projeto e o Acesso
    </h2>
    <p>O <strong>MrStock ERP</strong> é um projeto acadêmico desenvolvido pela equipe <strong>Mr. Coding</strong> para o Trabalho de Conclusão de Curso da ETEC Fernando Prestes. O projeto se inspira em rotinas do comércio de papelaria.</p>
    <p>As páginas de apresentação são públicas. O acesso ao ERP exige uma conta autorizada pela equipe responsável pelo ambiente. Use somente a conta atribuída a você e dados fictícios em demonstrações.</p>
</section>



<section id="termos-2" class="legal-section" tabindex="-1">
    <h2>
        2. Perfis de Acesso e Contas de Usuário (RBAC)
    </h2>
    <p>
        O MrStock ERP atribui permissões de acordo com o perfil da conta.
    </p>
    <div class="detail-grid">
        <div>
            <div class="detail-item">
                <h3>Perfil Administrador:</h3>
                <p>
                    O administrador tem acesso às funções de gestão, como configurações, cadastro de produtos, custos, relatórios e gestão de usuários.
                </p>
            </div>
        </div>
        <div>
            <div class="detail-item">
                <h3>Perfil Operador de Caixa:</h3>
                <p>
                    O operador de caixa registra vendas no PDV e consulta informações necessárias ao atendimento, como o preço de venda. O perfil de caixa tem acesso restrito a dados administrativos, como custos, margens e relatórios gerenciais.
                </p>
            </div>
        </div>
    </div>
    <p>
        Mantenha suas credenciais em sigilo. O sistema registra eventos de autenticação e determinadas operações para auditoria.
    </p>
</section>



<section id="termos-3" class="legal-section" tabindex="-1">
    <h2>
        3. Operação e Recursos do Sistema
    </h2>
    <ul>
        <li><strong>Frente de caixa:</strong> busca de produtos por código ou descrição, registro da forma de pagamento e cálculo de troco nas vendas em dinheiro.</li>
        <li><strong>Estoque e validade:</strong> acompanhamento de lotes e seus vencimentos. Para produtos com controle por lotes, a baixa prioriza o lote válido que vence primeiro (FEFO).</li>
        <li><strong>Compras e fornecedores:</strong> cadastro de fornecedores, registro de compras e acompanhamento de estoque mínimo.</li>
        <li><strong>Relatórios gerenciais:</strong> histórico de vendas e resumos de faturamento, conforme as permissões da conta.</li>
    </ul>
</section>



<section id="termos-4" class="legal-section" tabindex="-1">
    <h2>
        4. Emissão de Comprovantes de Venda
    </h2>
    <p>O MrStock gera comprovantes de venda para impressão. A NFC-e apresentada no ERP é uma <strong>simulação acadêmica</strong>, sem autorização da SEFAZ e sem valor fiscal.</p>
</section>



<section id="termos-5" class="legal-section" tabindex="-1">
    <h2>
        5. Disponibilidade do Ambiente
    </h2>
    <p>O ambiente web do projeto está disponível em <strong>mrstock.com.br</strong>. O acesso pode sofrer interrupções por manutenção ou indisponibilidade da infraestrutura.</p>
</section>



<section id="termos-6" class="legal-section" tabindex="-1">
    <h2>
        6. Propriedade Intelectual e Direitos Autorais
    </h2>
    <p>A equipe <strong>Mr. Coding</strong> desenvolveu o MrStock ERP. O código disponibilizado sob a <strong>licença MIT</strong> pode ser usado e redistribuído conforme os termos da licença. Dependências e materiais de terceiros seguem suas respectivas licenças.</p>
</section>



<section id="termos-7" class="legal-section" tabindex="-1">
    <h2>
        7. Canais de Suporte e Atendimento
    </h2>
    <p>
        Para suporte técnico, dúvidas de utilização ou encaminhamento de solicitações relativas ao sistema, estão disponíveis os canais oficiais de comunicação:
    </p>
    <div class="detail-grid">
        <div>
            <div class="detail-item">
                <div>WhatsApp Oficial:</div>
                <p>
                    <a href="https://wa.me/5515996441796?text=Ola%20equipe%20MrStock%2C%20preciso%20de%20suporte%20no%20ERP" target="_blank" rel="noopener noreferrer">
                        <strong>(15) 99644-1796</strong>
                    </a>
                </p>
            </div>
        </div>
        <div>
            <div class="detail-item">
                <div>E-mail Institucional:</div>
                <p>
                    <a href="mailto:contato@mrstock.com.br">
                        <strong>contato@mrstock.com.br</strong>
                    </a>
                </p>
            </div>
        </div>
    </div>
</section>




<?php require __DIR__ . '/inc/documento_legal_fim.php'; ?>
