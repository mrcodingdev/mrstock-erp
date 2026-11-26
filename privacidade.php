<?php
require_once __DIR__ . '/config.php';

$documentoLegal = 'privacidade';
$pageTitle = 'Política de Privacidade';
$descricaoLegal = 'Como o site e o ambiente acadêmico do MrStock ERP tratam dados.';
$metaDescricaoLegal = 'Política de Privacidade do MrStock ERP.';
$secoesLegais = [
    'Dados no Site e no ERP',
    'Finalidade do Tratamento',
    'Armazenamento e Segurança da Informação',
    'Compartilhamento de Dados',
    'Direitos do Titular de Dados',
    'Cookies e Tecnologias de Navegação',
    'Canal de Atendimento e Dúvidas',
];

require __DIR__ . '/inc/documento_legal_inicio.php';
?>

<section id="privacidade-1" class="legal-section" tabindex="-1">
    <h2>
        1. Dados no Site e no ERP
    </h2>
    <p>O <strong>MrStock ERP</strong> é um projeto acadêmico da equipe <strong>Mr. Coding</strong>. Esta política descreve o site de apresentação e a área de acesso restrito em <strong>mrstock.com.br</strong>.</p>
    <ul>
        <li><strong>Site público:</strong> as páginas de apresentação não pedem cadastro. Ao entrar em contato por e-mail ou WhatsApp, você envia à equipe os dados de contato e as informações que incluir na mensagem.</li>
        <li><strong>Área restrita:</strong> o login usa o nome de usuário e a senha informados. O sistema mantém dados da conta e pode registrar endereço IP e horário das tentativas de acesso.</li>
        <li><strong>Dados de demonstração:</strong> os cadastros e as operações comerciais usados para demonstrar o ERP são fictícios. Informações enviadas em contatos e dados de acesso à área restrita são tratados conforme descrito nesta política.</li>
    </ul>
</section>



<section id="privacidade-2" class="legal-section" tabindex="-1">
    <h2>
        2. Finalidade do Tratamento
    </h2>
    <p>
        As informações são usadas para responder a contatos, autenticar usuários e proteger o acesso às contas. O projeto adotou o Google Analytics para medir visitas e interações com o site.
    </p>
    <div class="detail-grid">
        <div>
            <div class="detail-item">
                <h3>Acesso e segurança</h3>
                <p>
                    As informações da conta permitem autenticar o usuário e aplicar as permissões do perfil. Os registros de tentativas de acesso ajudam a limitar falhas repetidas de login.
                </p>
            </div>
        </div>
        <div>
            <div class="detail-item">
                <h3>Contato com a equipe</h3>
                <p>
                    As informações enviadas pelo canal escolhido ajudam a equipe a entender a solicitação e responder.
                </p>
            </div>
        </div>
    </div>
</section>



<section id="privacidade-3" class="legal-section" tabindex="-1">
    <h2>
        3. Armazenamento e Segurança da Informação
    </h2>
    <p>O site está disponível por HTTPS. O ERP usa controles de sessão e permissões por perfil, além de limitar tentativas repetidas de login.</p>
</section>



<section id="privacidade-4" class="legal-section" tabindex="-1">
    <h2>
        4. Compartilhamento de Dados
    </h2>
    <p>O site usa a infraestrutura de hospedagem da Hostinger. Os links para o WhatsApp levam a um serviço externo. Se você enviar uma mensagem por esse canal, o WhatsApp trata as informações de acordo com sua própria política.</p>
    <p>Nas páginas em que a medição estiver ativa, o Google Analytics pode receber informações sobre a navegação, como páginas visitadas e dados do dispositivo ou navegador. Consulte <a href="https://policies.google.com/technologies/partner-sites?hl=pt-BR">como o Google usa dados de sites parceiros</a>.</p>
</section>



<section id="privacidade-5" class="legal-section" tabindex="-1">
    <h2>
        5. Direitos do Titular de Dados
    </h2>
    <p>Você pode pedir à equipe informações sobre o tratamento de seus dados pessoais, confirmação do tratamento, acesso ou correção de dados. Outros direitos, incluindo a eliminação de dados quando aplicável, seguem as condições do art. 18 da Lei Geral de Proteção de Dados Pessoais (LGPD).</p>
    <p>Para encaminhar uma solicitação, use o e-mail ou o WhatsApp indicados no final desta página.</p>
</section>



<section id="cookies" class="legal-section" tabindex="-1">
    <h2>
        6. Cookies e Tecnologias de Navegação
    </h2>
    <p>
        O site e o ERP usam recursos do navegador para manter a sessão e lembrar preferências:
    </p>
    <div>
        <table>
            <thead>
                <tr>
                    <th scope="col">Recurso Técnico</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Finalidade</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><code>PHPSESSID</code></strong></td>
                    <td><strong>Cookie de Sessão HTTP</strong></td>
                    <td>Manter a sessão do usuário entre as páginas da área restrita.</td>
                </tr>
                <tr>
                    <td><strong><code>mrstock_sidebar_state</code></strong></td>
                    <td><strong>Armazenamento Local (LocalStorage)</strong></td>
                    <td>Memorizar a preferência de visualização do menu lateral (expandido ou recolhido) sem oscilação visual.</td>
                </tr>
                <tr>
                    <td><strong><code>mrstock.landing.motion</code></strong></td>
                    <td><strong>Armazenamento Local (LocalStorage)</strong></td>
                    <td>Guardar no navegador a preferência de animações da apresentação pública.</td>
                </tr>
                <tr>
                    <td><strong><code>mrstock_analytics</code></strong></td>
                    <td><strong>Armazenamento local (LocalStorage)</strong></td>
                    <td>Lembrar se você permitiu ou recusou a medição de visitas.</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p>A medição é opcional. No painel <strong>Cookies e privacidade</strong>, escolha <strong>Permitir</strong>, <strong>Recusar</strong> ou <strong>Personalizar</strong>. O Google Analytics só é carregado na apresentação se você permitir a medição. Use <strong>Preferências de medição</strong> no rodapé da apresentação ou <button type="button" class="btn btn-sm btn-link p-0 align-baseline" data-preferencias-medicao hidden>altere suas preferências aqui</button>. A mesma escolha vale em todo o site.</p>
    <p>Você pode apagar essas preferências nas configurações do navegador. Apagar o cookie de sessão encerra o acesso no navegador.</p>
</section>



<section id="privacidade-7" class="legal-section" tabindex="-1">
    <h2>
        7. Canal de Atendimento e Dúvidas
    </h2>
    <p>
        A equipe Mr. Coding responde pelo site acadêmico e decide em conjunto sobre o tratamento dos dados. Para dúvidas sobre privacidade ou solicitações relativas a dados pessoais, entre em contato por e-mail ou WhatsApp.
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
