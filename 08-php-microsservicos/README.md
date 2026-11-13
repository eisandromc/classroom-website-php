# Versão 14 — Microsserviços PHP OOP + MySQLi
Três processos HTTP independentes: **gateway** (não conhece SQL), **serviço-catálogo** (DB `webshoes_catalogo`), **serviço-identidade** (DB `webshoes_identidade`). Cliente frontend usa `fetch()` somente no gateway, que encaminha requisições HTTP aos serviços. Cada microsserviço possui conexão MySQLi própria em `includes/ConexaoBanco.php`, com nomes de métodos em português.

## Preparação
1. Importar os SQL na ordem: `banco/catalogo_estrutura.sql`, `catalogo_dados.sql`, `identidade_estrutura.sql`, `identidade_dados.sql`. **As estruturas apagam tabelas existentes nos bancos indicados.** Os bancos são exclusivos à versão 14; não modificam `webshoes`.
2. Configure credenciais das conexões em `servico-catalogo/includes/ConexaoBanco.php` e `servico-identidade/includes/ConexaoBanco.php`.
3. Inicie **três terminais diferentes** na pasta `14-php-microsservicos`:

```bash
php -S 127.0.0.1:8114 -t servico-catalogo
php -S 127.0.0.1:8214 -t servico-identidade
php -S 127.0.0.1:8014 -t .
```
4. Loja: http://127.0.0.1:8014/frontend/index.html
5. Painel: http://127.0.0.1:8014/frontend/admin.html · admin/admin apenas local.
6. Health: http://127.0.0.1:8014/gateway/index.php?rota=saude

## Responsabilidades
- **Frontend:** HTML/CSS/JS separado; usa a API Gateway.
- **Gateway:** administra sessão, CSRF, login, upload e autorização; conversa com serviços via HTTP. Nenhum SQL.
- **Identidade:** somente login com `password_verify()` na tabela de usuários de seu banco próprio; endpoint reservado à rede interna.
- **Catálogo:** dono das tabelas `produtos` e `categorias`; GET público, CRUD interno autenticado por chave de serviço.
- **Upload:** gerenciado pelo gateway em `imagens/uploads`; exemplo local com diretório persistente no mesmo host.
- **Infraestrutura:** `configuracao.php` contém URLs fixas e segredo de serviço de LABORATÓRIO. Em qualquer implantação real, exigir segredos fortes em variáveis de ambiente, TLS entre serviços, isolamento de rede, gerenciamento de identidade, observabilidade e armazenamento de imagens independente.

## Limitações intencionais
- Microsserviços comunicam-se via HTTP sem broker, service discovery ou orquestração. Adequado ao ensino introdutório de limites e falhas distribuídas.
- O gateway constitui ponto único de acesso do navegador; serviço de identidade não é exposto pela vitrine.
- Sem pedidos, checkout, pagamento e filas. Não considerar arquitetura pronta para produção.
- Bases de dados separadas tornam esta implementação **independente** das outras versões. Compare usando a mesma semente inicial.
