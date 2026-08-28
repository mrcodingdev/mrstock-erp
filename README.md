<div align="center">
  <img src="assets/img/logo.png" alt="MrStock Logo" width="200"/>
  <h1>MrStock ERP</h1>
  <p><strong>Sistema de Gestão de Estoque e Frente de Caixa (PDV)</strong></p>

  [![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net/)
  [![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
  [![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com/)
</div>

## 📌 Sobre o Projeto
O **MrStock ERP** é um sistema completo de gestão comercial e controle de estoque, evoluído a partir de uma base de Frente de Caixa. O projeto destaca-se por possuir uma modelagem de dados avançada e preparada para escalabilidade corporativa.

A arquitetura do banco de dados foi projetada não apenas para gerenciar fluxos simples de entrada e saída, mas para suportar regras de negócios robustas exigidas em operações reais.

## 🚀 Principais Funcionalidades
- **Gestão de Compras (Entradas):** Sistema de Master-Detail para abastecimento de estoque com inserção dinâmica de múltiplos produtos.
- **Frente de Caixa (PDV):** Interface focada em conversão e agilidade de checkout.
- **Transações ACID:** Operações de compra e venda asseguradas via `PDO::beginTransaction()`, evitando corrupção de saldo no banco de dados.
- **Controle de Cadastros:** Clientes, Fornecedores e Categorias com formatação JS em tempo real (máscaras de CPF, CNPJ, CEP).
- **Segurança de Acesso:** Proteção Anti-CSRF em todos os formulários e segregação de perfis (Administrador vs Operador).

## 🛠 Tecnologias Utilizadas
- **Backend:** PHP (Comunicações via PDO, estrutura modular)
- **Banco de Dados:** MySQL (Consultas otimizadas, Integridade Referencial)
- **Frontend:** HTML5, CSS3, Bootstrap 5, Javascript Vanilla
- **Ícones e Assets:** FontAwesome 6

## ⚙️ Como Instalar e Rodar Localmente

1. Tenha instalado o [XAMPP](https://www.apachefriends.org/pt_br/index.html) (ou WAMP/MAMP).
2. Faça o clone deste repositório para dentro da sua pasta pública (`htdocs` ou `www`):
   ```bash
   git clone https://github.com/mrcodingdev/mrstock-erp.git
   ```
3. Inicie os módulos **Apache** e **MySQL** no painel do seu servidor local.
4. Abra o seu gerenciador de banco (ex: `phpMyAdmin`).
5. Importe o arquivo **`database/mrstock_db.sql`** (que já contém a estrutura e os dados base).
6. Acesse o sistema através do navegador: `http://localhost/nome-da-pasta/`

## 📚 Documentação Adicional
Diagramas, diários de bordo, justificativas técnicas e perguntas frequentes relativas à documentação do software podem ser encontrados no diretório `/docs`.

<hr>
<div align="center">
  Desenvolvido com dedicação para o Trabalho de Conclusão de Curso.
</div>
