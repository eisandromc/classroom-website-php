# API WebShoes v2.1 · PHP OOP / MySQLi

As rotas REST estão em `index.php`, **fora** das classes de domínio.
Os dados são acessados por `classes/Produto.php`, `Categoria.php`, `Usuario.php`, que importam `../includes/ConexaoBanco.php`.

## Endpoints públicos
- `GET ?rota=status` — processo HTTP online (não verifica MySQL)
- `GET ?rota=saude` — executa SELECT por meio da classe Categoria para verificar MySQL; 503 se indisponível
- `GET ?rota=categorias` — categorias ativas
- `GET ?rota=produtos` — produtos ativos; aceita `categoria`, `busca`, `ordem`
- `GET ?rota=produtos/1` — detalhe
- `GET ?rota=categorias/1/produtos` — produtos por categoria

## Autenticação para administração
- `GET ?rota=sessao` — retorna `{autenticado,token}` e estabelece cookie de sessão
- `POST ?rota=login` — JSON `{"usuario":"admin","senha":"admin"}` + header `X-CSRF-Token`
- `POST ?rota=logout` — header `X-CSRF-Token`

## Cadastros autenticados
- `GET ?rota=administracao/produtos` e `GET ?rota=administracao/categorias` — inclusive inativos
- `POST ?rota=produtos` e `POST ?rota=categorias` — cadastrar (JSON)
- `PUT ?rota=produtos/1` e `PUT ?rota=categorias/1` — editar (JSON completo)
- `DELETE ?rota=produtos/1` e `DELETE ?rota=categorias/1` — excluir
- `POST ?rota=imagens` — enviar `multipart/form-data` campo `imagem`; retorna caminho relativo para inserir no campo `imagem` do produto.

Toda operação de escrita exige cookie de sessão autenticado e `X-CSRF-Token` recebido da rota `sessao` ou `login`. Não há CORS aberto; a interface 03 deve estar na mesma origem. Senha armazenada em hash bcrypt. O login `admin/admin` é **exclusivo para ambiente local** e precisa ser alterado antes de publicar.
