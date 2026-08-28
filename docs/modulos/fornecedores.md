# Módulo de Gestão de Fornecedores

**Arquivos:** `fornecedores/index.php`, `fornecedores/functions.php`  
**Acesso:** Exclusivo para Administradores (`admin`)  
**Objetivo:** Gerenciar as indústrias, importadores e distribuidores parceiros da Papelaria Real (Tilibra, Bic, Faber-Castell, Acrilex, Chamex).

---

## 1. Funcionalidades Principais

- **Cadastro Completo:** Razão Social, Nome Fantasia, CNPJ, Inscrição Estadual, Telefone, E-mail e Endereço comercial.
- **Link Direto para Cotação no WhatsApp:** Abre o canal de atendimento do representante comercial do fornecedor.
- **Vínculo Obrigatório com Produtos:** Permite rastrear qual distribuidor fornece cada SKU do catálogo.
- **Tabela SalesOps:** Menu de 3 pontos para edição rápida e exclusão com proteção de chave estrangeira (`ON DELETE SET NULL`).