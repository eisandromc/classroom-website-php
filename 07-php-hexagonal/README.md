# Versão 12 — Arquitetura Hexagonal (Ports & Adapters)
O domínio e a aplicação dependem de interfaces, nunca de `mysqli`.
- `dominio/Portas` define os contratos.
- `aplicacao/Servico*` implementa casos de uso e recebe dependências no construtor.
- `adaptadores/MySQL` implementa os contratos e executa SQL via MySQLi; `adaptadores/Arquivos` cuida de upload.
- `includes/Dependencias.php` é a composition root e conecta as camadas.
- `index.php`, `categoria.php`, `produto.php`, `admin` são adaptadores de entrada HTTP (PHP/HTML integrado); mantêm login e CRUD.
- Reutiliza banco `webshoes` e `imagens/uploads` da pasta-raiz.
- Teste isolado sem banco: `php testes/testar_servico_sem_banco.php`.
- Web: `http://localhost/webshoes/12-php-hexagonal/`, painel `/admin/`.
