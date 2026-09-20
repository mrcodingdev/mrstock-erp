# Centro de Documentação Técnica: MrStock ERP v2.2.0

> Repositório central de especificações técnicas, manuais operacionais, modelagem de dados e anexos do Trabalho de Conclusão de Curso (TCC) da ETEC Fernando Prestes.

---

## Mapa da Documentação

A documentação do MrStock ERP está estruturada em quatro pilares principais:

### 1. Documentos Oficiais do TCC & Anexos
Documentos finais homologados para submissão e avaliação da banca examinadora:
* [Manual do Usuário Oficial v2.2.0 (PDF)](MRSTOCK_MANUAL_DO_USUARIO_v2.2.0_FINAL.pdf): Manual completo de 71 páginas com 86 ilustrações, sumário dinâmico e tutorial passo a passo.
* [Anexo A: Roteiro de Entrevista com o Cliente Piloto (PDF)](ANEXO_ENTREVISTA_LEVANTAMENTO_REQUISITOS_PAPELARIA_REAL.pdf): Instrumento de levantamento de requisitos com 24 perguntas e respostas chanceladas pelos fundadores da Papelaria Real Ltda.
* [Relatório Técnico de Banco de Dados (PDF)](RELATORIO_TECNICO_BANCO_DE_DADOS_NIKOLAS.pdf): Especificação do modelo relacional e integridade de dados.
* [Relatório Técnico do Módulo de Lotes e Validades (PDF)](RELATORIO_TECNICO_MODULO_LOTES_E_VALIDADES.pdf): Detalhamento do controle PEPS/FIFO e alerta visual preventivo de 30 dias.
* [Roteiro de Entrevista e Validação (PDF)](ROTEIRO_ENTREVISTA_VALIDACAO_PAPELARIA_REAL.pdf): Instrumento de pesquisa de campo preliminar.

---

### 2. Arquitetura e Engenharia de Software
Fundamentos técnicos, topologia do sistema e decisões de projeto:
* [Visão Geral do Sistema](visao_geral.md): Proposta de valor, contexto da Papelaria Real e diferenciais do projeto.
* [Estrutura do Projeto](estrutura_projeto.md): Mapeamento detalhado dos diretórios e arquivos da aplicação.
* [Stack Tecnológica & Justificativas](tecnologias_utilizadas.md): Tecnologias adotadas, versões e justificativas acadêmicas para o uso de PHP 8.2 nativo.
* [Modelagem do Banco de Dados](banco_de_dados.md): Estrutura das tabelas, chaves primárias/estrangeiras e tipos de dados.
* [Fluxos Transacionais do Sistema](fluxo_sistema.md): Diagramas de sequência de checkout no PDV e ciclo de vida do estoque.

---

### 3. Especificação dos Módulos da Aplicação
Documentação técnica de cada uma das funcionalidades implementadas:
* [Login & Autenticação](modulos/login.md)
* [Dashboard Gerencial](modulos/dashboard.md)
* [Frente de Caixa (PDV)](modulos/vendas_pdv.md)
* [Simulação Fiscal NFC-e & QR Code](modulos/nfce.md)
* [Emissão de Cupom Térmico](modulos/cupom.md)
* [Histórico de Vendas & Estorno](modulos/historico_vendas.md)
* [Catálogo de Produtos](modulos/produtos.md)
* [Kardex de Movimentações](modulos/movimentacoes.md)
* [Gerador de Etiquetas](modulos/etiquetas.md)
* [10 Famílias de Categorias](modulos/categorias.md)
* [Gestão de Compras](modulos/compras.md)
* [Gestão de Fornecedores](modulos/fornecedores.md)
* [Gestão de Clientes](modulos/clientes.md)
* [Centro de Análise (Curva ABC & DRE)](modulos/analise.md)
* [Central de Relatórios](modulos/relatorios.md)
* [Parâmetros & Configurações](modulos/configuracoes.md)
* [Navegação, Topbar & Design System](modulos/navegacao_e_layout.md)
* [Central de Ajuda](modulos/ajuda.md)

---

### 4. Qualidade, Testes & Apresentação na Banca
Critérios de homologação, garantia da qualidade e preparação para a banca:
* [Roteiro de Testes QTS](roteiro_testes_qts.md): Matriz de testes cobrindo os 22 casos de uso do sistema.
* [Perguntas da Banca Examinadora](perguntas_banca.md): Guia de respostas para arguições técnicas e de negócios.
* [Checklist de Revisão Final do TCC](revisao_final_tcc.md): Verificação pré-apresentação de requisitos, DER e usabilidade.
* [Roadmap & Melhorias Futuras](melhorias_futuras.md): Planejamento da versão 3.0 com migração para framework Laravel.

---

<div align="center">
  <sub>MrStock ERP v2.2.0 • ETEC Fernando Prestes • Sorocaba/SP</sub>
</div>
