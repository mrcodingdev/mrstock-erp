# Módulo de Demonstração de NFC-e (Nota Fiscal de Consumidor Eletrônica)

**Arquivos:** `vendas/nfce.php`  
**Acesso:** Administradores (`admin`)  
**Objetivo:** Apresentar a estrutura de layout e arquitetura de integração tributária para futura homologação com a SEFAZ.

---

## 1. Elementos da DANFE NFC-e Demonstrativa

- **Chave de Acesso Oficial:** Formato padrão de 44 dígitos com código da UF, ano/mês, CNPJ emitente, modelo 65, série e número.
- **Protocolo de Autorização:** Mock de resposta da SEFAZ com carimbo de data/hora.
- **QR Code Tributário:** Código bidimensional para consulta pública da nota fiscal via smartphone.
- **Cálculo Tributário Demonstrativo:** Discriminação de ICMS, PIS e COFINS pelo regime do Simples Nacional.