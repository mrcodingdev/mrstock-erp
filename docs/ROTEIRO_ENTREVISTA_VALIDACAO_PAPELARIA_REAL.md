# Roteiro Estruturado de Entrevista & Homologação de Usabilidade
## Sessão de Primeiro Contato com o Sistema • Papelaria Real
**Instituição:** ETEC Fernando Prestes — Centro Paula Souza  
**Curso:** Técnico em Desenvolvimento de Sistemas • TCC 2026  
**Equipe Mr. Coding:** Cesar Augusto • Douglas Moraes Braz • Eduardo Sugahara • Enzo Soares • Nikolas Pires  
**Público Entrevistado:** Proprietária e Operadora de Caixa da Papelaria Real  
**Ambiente Demonstrado:** Nuvem Oficial (`https://mrstock.com.br/`) e Contingência Local XAMPP  
**Local & Data:** Balcão da Papelaria Real • Sorocaba – SP • Setembro de 2026  

---

## 1. Diretrizes de Primeiro Contato & Divisão de Papéis

Como a proprietária e os funcionários da Papelaria Real **estão vendo o MrStock ERP pela primeira vez**, a dinâmica não deve pressupor conhecimento prévio do sistema. 

A abordagem ideal é:
1. **Mostrar Primeiro:** O Sugahara opera o sistema na tela, executando uma tarefa rápida e real.
2. **Deixar Olhar:** Dar alguns segundos para a cliente absorver o visual e a velocidade.
3. **Perguntar:** O Cesar pergunta como ela faz aquilo hoje na loja e se a forma como o MrStock faz atende, precisa de algo a mais ou se tem algo sobrando.

| Integrante Mr. Coding | Função na Sessão | Responsabilidade Prática |
| :--- | :--- | :--- |
| **Cesar Augusto** | **Condutor do Diálogo** | Abre cordialmente, contextualiza o TCC, faz as perguntas e anota as respostas. |
| **Eduardo Sugahara** | **Navegador do Sistema** | Demonstra o sistema ao vivo no notebook (simula bipagem no PDV, troco, busca e relatórios). |
| **Douglas Moraes Braz** | **Direção Técnica** | Esclarece dúvidas técnicas e avalia a viabilidade de pedidos de simplificação/mudança. |
| **Nikolas Pires** | **Anotador de Negócio** | Anota fornecedores, meios de pagamento e peculiaridades de produtos citados. |
| **Enzo Soares** | **Registrador de QTS** | Aplica a escala de usabilidade (notas 1 a 5) e registra fotos da visita com autorização. |

> **Fala de Abertura Sugerida (Cesar):**  
> *"Olá! Viemos apresentar pela primeira vez o MrStock ERP, o sistema que desenvolvemos especialmente para o comércio de papelaria. Queremos mostrar como ele funciona na prática e ouvir de você o que achou: se está fácil de entender, o que você mudaria, o que achou desnecessário e o que faltou para o seu dia a dia aqui no balcão."*

---

## 2. Bloco 1: Primeira Impressão Visual & Navegação

*O Sugahara abre a tela inicial do Dashboard e a listagem de produtos.*

1. **Legibilidade e Organização Visual:**  
   *"Ao bater o olho no sistema pela primeira vez, as informações estão claras? O tamanho das letras e dos números está confortável para enxergar no computador do balcão?"*  
   [ ] Resposta: ______________________________________________________________

2. **Ausência de Fotos de Produtos:**  
   *"Você reparou que não colocamos fotos dos cadernos e canetas, apenas nome, código e preço, para o sistema abrir instantâneo e não travar. Você sente falta de fotos ou prefere assim, limpo e direto?"*  
   [ ] Resposta: ______________________________________________________________

---

## 3. Bloco 2: Frente de Caixa (PDV) & Operação de Balcão

*O Sugahara abre a tela de vendas (PDV), bipa um produto com código de barras, adiciona 3 canetas, abre o pagamento em dinheiro, digita uma nota de R$ 50,00 e mostra o cálculo do troco e a emissão do cupom.*

3. **Rotina de Entrada no Caixa (Leitor vs. Digitação):**  
   *"Hoje vocês usam leitor de código de barras físico no caixa? Quando o código não lê ou o produto não tem código, você acha fácil buscar digitando o nome do produto no campo de busca como mostramos agora?"*  
   [ ] Resposta: ______________________________________________________________

4. **Cálculo Automático de Troco e Cédulas Rápidas:**  
   *"Mostramos os botões de notas rápidas (R$ 20, R$ 50, R$ 100) e o cálculo automático do troco na tela. Isso no dia a dia ajudaria a evitar erros ou a conferência mental já basta?"*  
   [ ] Resposta: ______________________________________________________________

5. **Formas de Pagamento e Descontos:**  
   *"Vocês costumam dar desconto quando o cliente pede (ex: compra da lista de material completa ou pagamento em dinheiro/Pix)? Vocês dão desconto em porcentagem (ex: 5%) ou tiram um valor em reais (ex: tirar R$ 5,00)?"*  
   [ ] Resposta: ______________________________________________________________

6. **Identificação do Consumidor:**  
   *"No balcão de vocês, a maioria dos clientes compra e vai embora rápido sem passar CPF, ou vocês precisam cadastrar o cliente na hora da venda?"*  
   [ ] Resposta: ______________________________________________________________

---

## 4. Bloco 3: Particularidades da Papelaria (Fracionados, Validades e Etiquetas)

*O Sugahara mostra a tela de produtos, a aba de lotes com vencimento e o gerador de etiquetas.*

7. **Venda Fracionada (Itens Vendidos por Metro):**  
   *"Vocês vendem itens cortados por metro na papelaria (como plástico contact, papel kraft, fitas ou EVA), ou absolutamente tudo que vocês vendem é por unidade inteira, folha ou caixa fechada?"*  
   [ ] Resposta: ______________________________________________________________

8. **Controle de Validades de Produtos Químicos:**  
   *"Vocês costumam ter problemas com produtos vencendo na loja (tintas guache, colas líquidas, massinhas de modelar, corretivos)? O nosso alerta de validade em 30 dias é útil para vocês?"*  
   [ ] Resposta: ______________________________________________________________

9. **Produtos Pequenos Sem Código de Fábrica:**  
   *"Canetas avulsas, lápis e borrachas soltas vêm sem código. Como vocês cobram isso no caixa hoje? O gerador de etiquetas que imprime códigos de barras em folhas comuns A4 ajudaria vocês?"*  
   [ ] Resposta: ______________________________________________________________

---

## 5. Bloco 4: Compras, Fornecedores & Entrada de Estoque

*O Sugahara abre o módulo de Ordens de Compra e mostra o espelho de conferência impresso.*

10. **Rotina de Entrada de Notas:**  
    *"Quando chegam caixas de distribuidores (Tilibra, Chamex, Faber-Castell), como vocês lançam no estoque? Vocês digitam produto por produto ou recebem o arquivo XML da Nota Fiscal?"*  
    [ ] Resposta: ______________________________________________________________

11. **Conferência Física de Mercadorias:**  
    *"O sistema gera uma folha de conferência de compra para imprimir e ir ticando as caixas que chegam do caminhão. Vocês costumam conferir no papel o que chegou antes de colocar na prateleira?"*  
    [ ] Resposta: ______________________________________________________________

---

## 6. Bloco 5: Práticas Comerciais, Fiado & Segurança RBAC

12. **Venda a Prazo / 'Caderninho' / Convênio Escolar:**  
    *"Vocês vendem fiado ou anotado no caderno para escolas, escritórios ou clientes conhecidos pagarem no fim do mês? Ou todo mundo paga no ato via Pix, Dinheiro ou Cartão?"*  
    [ ] Resposta: ______________________________________________________________

13. **Sigilo de Informações dos Funcionários no Caixa:**  
    *"No MrStock, quem opera o caixa só consegue vender: não consegue ver o preço que você pagou pelo produto, não vê seu lucro nem os relatórios. Você aprova essa segurança ou prefere que o operador veja tudo?"*  
    [ ] Resposta: ______________________________________________________________

14. **Fechamento de Caixa e Sangrias:**  
    *"No fim do dia, vocês contam o dinheiro da gaveta para bater com o total vendido? Costumam tirar dinheiro durante o dia para pagar entregas ou despesas rápidas da loja (sangria)?"*  
    [ ] Resposta: ______________________________________________________________

---

## 7. Bloco 6: Avaliação de Usabilidade (Primeiro Contato com o Sistema)

*Solicite à proprietária uma nota de **1 a 5** com base nas primeiras impressões da demonstração:*

| Afirmação Avaliada | 1 (DT) | 2 (D) | 3 (N) | 4 (C) | 5 (CT) | Comentário Espontâneo da Cliente |
| :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| **1.** O sistema parece fácil de aprender e utilizar no dia a dia. | [ ] | [ ] | [ ] | [ ] | [ ] | |
| **2.** A tela de venda (PDV) parece rápida para atender filas grandes. | [ ] | [ ] | [ ] | [ ] | [ ] | |
| **3.** As informações na tela estão organizadas e sem poluição visual. | [ ] | [ ] | [ ] | [ ] | [ ] | |
| **4.** O cálculo de troco automático facilita a rotina do operador. | [ ] | [ ] | [ ] | [ ] | [ ] | |
| **5.** A busca por nome do produto responde com rapidez satisfatória. | [ ] | [ ] | [ ] | [ ] | [ ] | |
| **6.** Os números e relatórios mostram o que o dono precisa saber. | [ ] | [ ] | [ ] | [ ] | [ ] | |
| **7.** Eu utilizaria o MrStock ERP como o sistema oficial da papelaria. | [ ] | [ ] | [ ] | [ ] | [ ] | |

*(Legenda: 1 = Discordo Totalmente | 2 = Discordo | 3 = Neutro | 4 = Concordo | 5 = Concordo Totalmente)*

---

## 8. Bloco 7: Feedback Aberto — O que Adicionar, Simplificar ou Remover?

1. **Teve alguma coisa que você achou COMPLICADA ou que preferiria ver mais simples?**  
   *Anotação:* ____________________________________________________________________  
   ________________________________________________________________________________

2. **Teve alguma informação ou botão que você achou SOBRANDO (desnecessário para a sua loja)?**  
   *Anotação:* ____________________________________________________________________  
   ________________________________________________________________________________

3. **Faltou alguma coisa que você usa todo dia na loja e gostaria muito que tivesse aqui?**  
   *Anotação:* ____________________________________________________________________  
   ________________________________________________________________________________

4. **Qual impressora vocês têm no balcão hoje para imprimir cupom ou comprovante?**  
   [ ] Térmica Bobina 80mm (Epson / Bematech / Elgin)  
   [ ] Impressora Comum de Folha A4  
   [ ] Não possui impressora no caixa  

---

## 9. Termo de Participação & Homologação

> *Declaramos que a equipe técnica Mr. Coding apresentou presencialmente a versão funcional do sistema MrStock ERP v2.2.0 nas dependências da Papelaria Real, colhendo feedbacks para o Trabalho de Conclusão de Curso da ETEC Fernando Prestes.*

\
__________________________________________________  
**Representante da Papelaria Real**  
Proprietária / Gerente Geral  

\
__________________________________________________  
**Equipe Mr. Coding**  
ETEC Fernando Prestes — Sorocaba/SP  
