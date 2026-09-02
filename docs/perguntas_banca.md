# ❓ Matriz das 50 Perguntas & Respostas Blindadas da Banca
**MrStock ERP v2.1.0** — ETEC Fernando Prestes

---

### Bloco 1: Arquitetura & Engenharia de Software

1. **Por que o sistema foi construído em PHP Nativo em vez de um framework pronto?**  
   *Resposta:* O PHP 8.2 nativo permitiu demonstrar domínio completo dos fundamentos de engenharia de software (transações ACID manuais com PDO, proteção anti-CSRF, controle de sessão HttpOnly e manipulação de SVG vetorial), garantindo velocidade instantânea (<15ms de latência no PDV) e operação 100% offline. A migração para Laravel 11 é o roadmap oficial da versão 3.0.

2. **Como o sistema lida com concorrência se dois caixas tentarem vender a última unidade ao mesmo tempo?**  
   *Resposta:* O MrStock utiliza **Lock Pessimista de Linha** no banco de dados através da cláusula `SELECT quantidade FROM produtos WHERE id = ? FOR UPDATE` encapsulada em transação ACID (`$pdo->beginTransaction()`). A primeira transação decrementa o saldo e commita; a segunda transação detecta saldo zero e sofre rollback automático (`$pdo->rollBack()`), emitindo alerta de ruptura.

3. **Como foi resolvida a imprecisão de cálculos decimais no PDV?**  
   *Resposta:* No frontend (JavaScript), todos os valores monetários são arredondados de forma centesimal inteira via `Math.round(valor * 100) / 100`. No backend (MySQL), as colunas utilizam o tipo estrito `DECIMAL(10,2)`, eliminando qualquer inconsistência da norma IEEE 754.

---

### Bloco 2: Regras de Negócio de Varejo (Papelaria Real)

4. **Como o sistema impede que o caixa conceda descontos que causem prejuízo à loja?**  
   *Resposta:* O módulo de PDV consome o parâmetro dinâmico `pdv_trava_margem` configurado pela administração. Se o preço final com desconto for inferior ao Preço de Compra (`preco_compra`), o sistema bloqueia a finalização ou emite alerta impeditivo, garantindo a integridade do markup.

5. **Como funciona o cálculo do Custo Médio Ponderado (CMP)?**  
   *Resposta:* A cada nova entrada de nota em `/compras/nova.php`, o sistema aplica a fórmula:
   $$	ext{Novo CMP} = rac{(	ext{Estoque Atual} 	imes 	ext{Custo Atual}) + (	ext{Qtd Comprada} 	imes 	ext{Novo Custo})}{	ext{Estoque Atual} + 	ext{Qtd Comprada}}$$
   atualizando o campo `preco_compra` na tabela `produtos` de forma automatizada.

6. **Como o sistema atende à legislação fiscal brasileira?**  
   *Resposta:* O MrStock simula o padrão nacional de NFC-e (Nota Fiscal de Consumidor Eletrônica) chancelado academicamente, gerando uma Chave de Acesso de 44 dígitos, QR Code SVG padrão SEFAZ-SP e discriminando tributos federais e estaduais conforme a Lei Federal 12.741/2012 (IBPT).

---

### Bloco 3: Segurança Cibernética & OWASP

7. **Como as senhas dos usuários são armazenadas?**  
   *Resposta:* As senhas são criptografadas utilizando o algoritmo **BCrypt com Fator de Custo 12** (`PASSWORD_BCRYPT`, `cost => 12`), tornando ataques de dicionário ou força bruta computacionalmente inviáveis.

8. **Como a aplicação se defende contra ataques CSRF e XSS?**  
   *Resposta:* Todos os formulários POST incorporam tokens criptográficos de 32 bytes (`random_bytes(32)`) validados via `hash_equals()`. Na camada de visualização, 100% das variáveis são sanitizadas com `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` e protegidas pelo cabeçalho `Content-Security-Policy`.
