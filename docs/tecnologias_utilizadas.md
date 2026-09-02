# 💻 Stack Tecnológica & Decisões Arquiteturais
**MrStock ERP v2.1.0** — Edição Papelaria Real

---

## 1. Visão Geral da Stack

| Camada | Tecnologia Adotada | Versão | Justificativa Técnica / Acadêmica |
| :--- | :--- | :---: | :--- |
| **Backend Runtime** | PHP Nativo | `8.2.x` | Desempenho bruto, tipagem estrita, match expressions, sem sobrecarga de frameworks para o TCC. |
| **Persistência de Dados**| MariaDB / MySQL | `10.4.x` | Engine InnoDB transacional (ACID), integridade referencial e suporte a Lock Pessimista (`FOR UPDATE`). |
| **Abstração de Banco** | PHP Data Objects (PDO) | Nativo | Prepared Statements imunes a SQL Injection, transações gerenciadas e tipagem segura. |
| **Frontend Framework** | Bootstrap (Custom Sólido)| `5.3.x` | Responsividade, grid modular e botões 100% sólidos sem dependência de temas externos. |
| **Visualização de Dados**| Chart.js | `4.4.x` | Gráficos interativos em Canvas (Receita vs Custo, Curva ABC) com rendering acelerado por hardware. |
| **Sonoplastia do PDV** | Web Audio API | W3C Standard | Síntese acústica em tempo real (880Hz / 280Hz) sem arquivos pesados .mp3 e 100% offline. |
| **Geração de Códigos** | SVG Vetorial Nativo | RFC Standard | Algoritmo matemático puro em PHP para Code 128B e QR Code NFC-e em vetor ultraleve (<3.4 KB). |
| **Tipografia Corporativa**| Inter Font Family | Auto-hospedada| `font-variant-numeric: tabular-nums` para alinhamento contábil e anti-FOUC. |
| **Compilação de PDFs** | Microsoft Edge Headless | Chromium Engine| `--headless --print-to-pdf` de alta precisão vetorial sem dependência de extensões instáveis. |
| **Servidor Web Local** | Apache HTTP Server | `2.4.x` (XAMPP)| Ambiente LAMP local para contingência e desenvolvimento ativo. |
| **Hospedagem em Nuvem** | ProFreeHost / VistaPanel| Produção | Acesso web via `http://mrstock.unaux.com/` com chaveamento híbrido Zero-Config. |

---

## 2. Decisão Arquitetural: PHP 8.2 Nativo vs Frameworks (Laravel)

### Por que PHP 8.2 Nativo no TCC Oficial?
1. **Didática e Transparência na Banca:** Permite aos avaliadores inspecionar a engenharia de software pura: transações manuais via PDO, sessões nativas, manipulação de streams e segurança em baixo nível.
2. **Desempenho Extremo no PDV:** Tempo de inicialização e resposta de requisição inferior a **15ms**, essencial para filas de caixa em papelaria.
3. **Resiliência Offline Total:** Zero complexidade de containers ou compilação de assets em tempo de execução no PDV.

> **Roadmap v3.0:** A migração para **Laravel 11** com arquitetura em camadas (Repository Pattern, DTOs e Eloquent ORM) está mapeada formalmente como **Trabalhos Futuros / Evolução Tecnológica**.
