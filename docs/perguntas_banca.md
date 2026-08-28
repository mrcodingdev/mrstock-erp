# Guia de Preparação e Perguntas da Banca — MrStock ERP v2.0

Este documento reúne as principais perguntas técnicas, conceituais e de arquitetura que a banca avaliadora da ETEC Fernando Prestes pode formular durante a apresentação do TCC, acompanhadas das respostas ideais fundamentadas nas decisões de engenharia do projeto.

---

## 🏛️ Bloco 1: Arquitetura, Segurança e Concorrência

### P1: "Como o sistema lida com o problema de concorrência se dois caixas tentarem vender o último produto do estoque ao mesmo tempo?"
> **Resposta:**  
> O MrStock utiliza uma estratégia de **Bloqueio Pessimista (*Pessimistic Locking*)** no banco de dados. Ao iniciar o processamento da venda dentro de uma transação PDO (`$pdo->beginTransaction()`), executamos a consulta com a cláusula `SELECT ... FOR UPDATE`. Isso instrui o MySQL/InnoDB a bloquear a linha do produto até o `commit()` ou `rollBack()`. O segundo caixa aguarda o encerramento da primeira transação e, ao receber a linha, constata que o estoque está zerado, abortando a venda com segurança sem gerar estoque negativo (*Race Condition*).

---

### P2: "Como foi garantida a proteção contra ataques de injeção de SQL e CSRF?"
> **Resposta:**  
> - **SQL Injection:** 100% das consultas utilizam Prepared Statements do PDO com emulação desativada (`PDO::ATTR_EMULATE_PREPARES => false`). Dessa forma, os dados nunca são concatenados diretamente na instrução SQL, eliminando qualquer possibilidade de injeção.
> - **CSRF:** Todo formulário submetido via POST inclui um token criptográfico gerado por `bin2hex(random_bytes(32))` armazenado na sessão. O backend valida a requisição utilizando `hash_equals()`, garantindo que requisições forjadas por sites externos sejam sumariamente rejeitadas com erro HTTP 403.

---

### P3: "Por que optar por PHP 8.2 puro estruturado/modular em vez de um framework como Laravel?"
> **Resposta:**  
> A escolha foi baseada em requisitos operacionais e de portabilidade:  
> 1. **Performance e Baixo Consumo de Recursos:** A aplicação roda instantaneamente em qualquer máquina modesta com XAMPP sem sobrecarga de memória.  
> 2. **Portabilidade Plug-and-Play:** A estrutura pode ser copiada diretamente para um pendrive ou hospedagem simples sem etapas complexas de compilação ou dependências do Composer.  
> 3. **Domínio Arquitetural:** Construir os middlewares, controle de sessão e transações do zero demonstra o domínio pleno dos fundamentos de engenharia de software pela equipe.

---

## 🎨 Bloco 2: Frontend, UX e Design System SalesOps

### P4: "Por que foi utilizada a Web Audio API nativa em vez de um arquivo de áudio MP3/WAV no PDV?"
> **Resposta:**  
> Arquivos de áudio tradicionais exigem requisições HTTP adicionais, geram latência de carregamento e podem falhar caso a conexão caia. A **Web Audio API** sintetiza o áudio via hardware através de um oscilador matemático em onda senoidal pura (880Hz) diretamente no navegador do cliente, com tempo de resposta de 0 milissegundos, consumo nulo de banda e funcionamento 100% offline garantido.

---

### P5: "Como foi resolvido o problema da tela 'piscar' ao mudar de página com a sidebar colapsada (Anti-FOUC)?"
> **Resposta:**  
> Implementamos um script JavaScript síncrono inline posicionado estrategicamente no `<head>` de `inc/header.php`. Antes de renderizar o HTML da página, o script lê o estado no `localStorage` e injeta a classe `.sidebar-collapsed` na tag `<html>`. Quando o navegador desenha o primeiro frame na tela, o layout já está na largura correta (72px), eliminando o efeito de piscamento (*Flash of Unstyled Content*).

---

### P6: "Como funciona a geração de códigos de barras sem bibliotecas de terceiros?"
> **Resposta:**  
> Desenvolvemos o `inc/barcode_helper.php`, um gerador vetorial puro em PHP que calcula os padrões de barras e espaços da norma **Code-128 (B)** e **EAN-13**, inserindo o caractere de checksum ponderado (módulo 103) e retornando o desenho em markup **SVG puro**. Isso permite que o código de barras seja impresso com nitidez vetorial infinita em impressoras térmicas de qualquer resolução (DPI).