# 🧪 Roteiro de Qualidade e Teste de Software (QTS)
**MrStock ERP v2.1.0** — Matriz de Cobertura dos 22 Casos de Uso (UC001 a UC022)

---

## 1. Resumo Executivo da Bateria de Testes

- **Casos de Uso Mapeados:** 22 Casos de Uso Formais
- **Cenários de Teste Executados:** 36 Cenários
- **Casos de Teste (TCs) Aprovados:** 48/48 (Taxa de Sucesso: **100% PASS**)
- **Testes Automatizados de Banco/Parâmetros:** 21/21 Parâmetros validados (**100% PASS**)

---

## 2. Matriz Completa dos 22 Casos de Uso

| Caso de Uso | Módulo / Tela | Cenário Avaliado | Resultado |
| :--- | :--- | :--- | :---: |
| **UC001** | Autenticação | Login com credenciais válidas e inválidas, bloqueio de sessão | 🟢 **PASS** |
| **UC002** | Dashboard | Carregamento de KPIs em Bento Grid e monitor de vencimento | 🟢 **PASS** |
| **UC003** | Venda Rápida | Lançamento expresso no dashboard com baixa de estoque | 🟢 **PASS** |
| **UC004** | PDV Bipagem | Leitor de código de barras com som Web Audio API em <15ms | 🟢 **PASS** |
| **UC005** | PDV Desconto | Concessão de desconto com trava de margem e atalho F7 | 🟢 **PASS** |
| **UC006** | PDV Checkout | Troco exato centesimal e pagamento multiformas (F4) | 🟢 **PASS** |
| **UC007** | Cupom Fiscal | Emissão térmica 80mm/58mm/A4 com QR Code SVG SEFAZ-SP | 🟢 **PASS** |
| **UC008** | Histórico Vendas | Filtragem por período e estorno ACID com devolução de saldo | 🟢 **PASS** |
| **UC009** | Produtos Cadastro| Inclusão de item com cálculo automático de markup | 🟢 **PASS** |
| **UC010** | Produtos Estoque | Alerta visual de estoque mínimo e validade de 30 dias | 🟢 **PASS** |
| **UC011** | Movimentações | Registro forense de entradas, devoluções e perdas | 🟢 **PASS** |
| **UC012** | Etiquetas | Emissão de etiquetas Code 128B em SVG vetorial | 🟢 **PASS** |
| **UC013** | Categorias | Gestão das 10 famílias com integridade referencial | 🟢 **PASS** |
| **UC014** | Compras Entrada | Entrada de nota fiscal com recálculo automático de CMP | 🟢 **PASS** |
| **UC015** | Compras Status | Gerenciamento de status financeiro (PAGA/PENDENTE) | 🟢 **PASS** |
| **UC016** | Fornecedores | Homologação de fornecedores e botão WhatsApp circular | 🟢 **PASS** |
| **UC017** | Clientes | Cadastro de clientes com validação de CPF e busca CEP | 🟢 **PASS** |
| **UC018** | Relatórios DRE | Apuração gerencial de Faturamento, CMV e Lucro Bruto | 🟢 **PASS** |
| **UC019** | Relatórios Curva ABC| Classificação de produtos em Curva 80-15-5 com Chart.js | 🟢 **PASS** |
| **UC020** | Relatórios PDF | Emissão de relatório executivo formatado para folha A4 | 🟢 **PASS** |
| **UC021** | Configurações | Persistência de 21 parâmetros e Backup SQL em 1-clique | 🟢 **PASS** |
| **UC022** | Central de Ajuda | Live search em tempo real e mesa tátil de atalhos <kbd> | 🟢 **PASS** |
