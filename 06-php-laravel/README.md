# Versão 10 · WebShoes com Laravel 13 (MVC + Eloquent)
O projeto contém código-fonte Laravel, controllers, Models, Views Blade, rotas, middleware e sessões; **não contém vendor/**: bibliotecas instaladas com Composer.
Pré-requisitos: **PHP 8.3+**, Composer 2, extensões exigidas pelo Laravel (incluindo PDO MySQL). **Exceção didática:** Laravel usa drivers próprios (PDO internamente), diferente das versões PHP sem framework que usam MySQLi.

## Início rápido
Com o MySQL rodando, execute dentro de `06-php-laravel`:
- **Windows:** `iniciar.bat` (ou dois cliques no arquivo)
- **Linux/macOS:** `./iniciar.sh`

O script verifica PHP 8.3+ e extensões, instala o Composer localmente (`composer.phar`) se não houver um no sistema, roda `composer install` se faltar `vendor/`, cria `.env`, pastas de `storage/` e `APP_KEY`, confere o banco `webshoes` (oferece importar `../00_banco/*.sql` **somente se o banco ainda não existir**) e inicia o servidor em http://127.0.0.1:8010.
Opções: `--preparar` só prepara, sem iniciar; variável `PORTA` muda a porta. A lógica comum fica em `scripts/verificar_ambiente.php`.

## Instalação manual
1. Importe **`../00_banco/estrutura.sql`** e **`../00_banco/dados.sql`** apenas no ambiente novo (estrutura destrói dados anteriores); se banco `webshoes` já existe, reutilize-o e não reimporte.
2. Dentro de `06-php-laravel`: `composer install`.
3. Copie `.env.exemplo` para `.env`; ajuste DB_DATABASE=webshoes, DB_USERNAME, DB_PASSWORD. `APP_URL=http://localhost:8010`.
4. `php artisan key:generate`
5. `php artisan serve --host=127.0.0.1 --port=8010`
6. Loja: http://127.0.0.1:8010/ · Admin: http://127.0.0.1:8010/admin/login (`admin`/`admin` apenas local).
O framework deve servir **somente a pasta `public/`**, nunca a raiz do projeto. `artisan serve` faz isso automaticamente.

## Comparação
- ORM Eloquent; relacionamento Categoria `hasMany` Produto `belongsTo`.
- Formulários Blade, validação por Request, middleware, CSRF, sessões, upload sem confiar no nome do arquivo.
- Usa a base `webshoes` e `../imagens/uploads`, como versões 01–05/12. Imagens SVG iniciais no diretório `public/assets/img`.
- Sem integração com a API da versão 04. Login por tabela `usuarios_administrativos`.

**Validação do framework:** sintaxe do código disponível verificada; `composer install`, execução Artisan e comunicação real com MySQL precisam ser testadas em ambiente com dependências.
