# WebShoes Architecture Lab — 8 projetos independentes

**Entrega atual:** preserva os projetos 01–04 e acrescenta **05**, **10**, **12** e **14**. Os números 06–09, 11 e 13 são apenas posições reservadas na trilha didática e **não** foram gerados.

| Pasta | Projeto | Persistência | Interface | Painel/CRUD |
| --- | --- | --- | --- | --- |
| 01-php-oop | PHP OOP integrado ao HTML | MySQLi direto em classes | PHP/HTML | Sim, autenticação e upload |
| 02-php-mvc | PHP MVC | MySQLi direto em Modelos | Views PHP | Sim, autenticação e upload |
| 03-frontend-api | JavaScript e REST | API 04 exclusivamente | HTML/CSS/JS | Sim, via API 04 |
| 04-api-rest | API REST PHP OOP | MySQLi em classes | JSON HTTP | Endpoints GET/POST/PUT/PATCH/DELETE/upload |
| 05-php-procedural | PHP procedural | `mysqli_*` em funções | PHP/HTML | Sim, autenticação e upload |
| 10-php-laravel | Laravel 13 MVC/Eloquent | Framework ORM (usa PDO internamente) | Blade | Sim, autenticação e upload |
| 12-php-hexagonal | PHP Ports & Adapters | MySQLi em adaptador | PHP/HTML | Sim, autenticação e upload |
| 14-php-microsservicos | Gateway + serviços HTTP separados | MySQLi em bancos próprios | HTML/JS + Gateway | Sim, REST com sessão e upload |

## Premissas importantes
- As versões **01, 02, 05 e 12** consultam MySQL **diretamente**, sem usar API, com base `webshoes`.
- A versão **03** consulta **somente a API 04**. Ela não acessa banco diretamente.
- A versão **04** expõe as rotas HTTP e faz SQL por classes MySQLi com regras de autenticação.
- A versão **10** usa Laravel 13 e Eloquent (o framework utiliza PDO por baixo). É a exceção inevitável à regra sem PDO dos exemplos PHP puro. Requer Composer 2, PHP 8.3+, extensões requeridas pelo Laravel e acesso aos pacotes Composer na máquina de instalação.
- A versão **14** possui seu **próprio catálogo** (`webshoes_catalogo`) e **seu próprio serviço de identidade** (`webshoes_identidade`), cada serviço em um processo HTTP separado; o gateway não usa SQL. É um laboratório de fronteiras de microsserviços, não arquitetura corporativa de produção.
- Todas as versões usam o mesmo conjunto **inicial** de dados, mas o serviço 14 pode divergir depois de cadastrado, por ter armazenamento separado.
- A loja inclui catálogo, busca, categorias, produto, admin, cadastro/edição/exclusão e upload de imagem. Sacola é demonstração sem pagamentos reais.

## Executar no XAMPP
Para 01–05 e 12: extraia todo o laboratório em `C:\\xampp\\htdocs\\webshoes`; importe o SQL em `banco/estrutura.sql` e depois `banco/dados.sql` **apenas em instalação nova**. Estes arquivos apagam tabelas existentes do banco `webshoes`.

URLs:
- 01 — `http://localhost/webshoes/01-php-oop/` — admin: `/admin/`
- 02 — `http://localhost/webshoes/02-php-mvc/` — admin: `/index.php?pagina=admin`
- 03 — `http://localhost/webshoes/03-frontend-api/` — admin: `/admin.html`
- 04 — `http://localhost/webshoes/04-api-rest/index.php?rota=saude`
- 05 — `http://localhost/webshoes/05-php-procedural/` — admin: `/admin/`
- 12 — `http://localhost/webshoes/12-php-hexagonal/` — admin: `/admin/`

Versão 10: veja [10-php-laravel/README.md](10-php-laravel/README.md). Ela deve ser servida por `artisan serve` ou document root apontando **somente** para `public/`.

Versão 14: veja [14-php-microsservicos/README.md](14-php-microsservicos/README.md); execute **3 servidores PHP** e importe os seus dois bancos separados. Não funciona com apenas Apache e sem iniciar os processos de serviço.

**Login demonstrativo**: `admin` / `admin`, apenas em laboratório local; nunca expor isso publicamente. Hash bcrypt já incluído em `banco/dados.sql` e `14-php-microsservicos/banco/identidade_dados.sql`. Não publicar com root sem senha.

## Estrutura comparativa para aula
1. Mesmo problema e contrato CRUD; compare separação de responsabilidades e fluxo das requisições.
2. Versões 05 (procedural), 01 (OOP), 02 (MVC), 12 (hexagonal), 10 (Laravel) enfatizam diferentes fronteiras de código e persistência.
3. Versão 03/04 diferencia cliente web e servidor REST; a 14 adiciona chamadas entre processos, separação de banco por serviço e falhas distribuídas.
4. Teste a versão 12 sem banco com `php 12-php-hexagonal/testes/testar_servico_sem_banco.php`.
5. Examine `GUIA_COMPARATIVO_EXPANDIDO.md` e `VALIDACAO_EXPANDIDA.md`.

**Restrições de validação:** o ambiente de criação não tem Composer/vendor, extensão MySQLi real ou servidor MySQL real. Há verificações de sintaxe, testes unitários sem banco e testes HTTP com simulação (detalhados em `VALIDACAO_EXPANDIDA.md`). Não há validação integrada real de Laravel ou MySQL em produção.

**EER de microsserviços:** `14-php-microsservicos/banco/modelo_eer_microsservicos.png`, com fontes `.dbml`, `.dot` e `.svg`.
