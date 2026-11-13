/* Serviço REST compartilhado pela vitrine e pelo painel de administração. */
async function requisitarApi(rota, { metodo = 'GET', parametros = {}, dados, token = '' } = {}) {
    const endereco = new URL(ENDERECO_API, window.location.href);
    endereco.searchParams.set('rota', rota);
    Object.entries(parametros).forEach(([chave, valor]) => {
        if (valor !== null && valor !== undefined && valor !== '') endereco.searchParams.set(chave, String(valor));
    });
    const cabecalhos = { Accept: 'application/json' };
    if (token) cabecalhos['X-CSRF-Token'] = token;
    let corpo;
    if (dados instanceof FormData) corpo = dados;
    else if (dados !== undefined) { cabecalhos['Content-Type'] = 'application/json'; corpo = JSON.stringify(dados); }
    const resposta = await fetch(endereco, { method: metodo, credentials: 'same-origin', headers: cabecalhos, body: corpo });
    let conteudo;
    try { conteudo = await resposta.json(); }
    catch { throw new Error('A API não retornou JSON. Verifique o servidor PHP.'); }
    if (!resposta.ok) {
        const erro = new Error(conteudo.erro || `Erro HTTP ${resposta.status}`);
        erro.status = resposta.status;
        throw erro;
    }
    return conteudo;
}
async function buscarDados(rota, parametros = {}) { return requisitarApi(rota, { parametros }); }
const ServicoWebShoes = {
    listarCategorias: () => buscarDados('categorias'),
    listarProdutos: (parametros = {}) => buscarDados('produtos', parametros),
    buscarProduto: codigo => buscarDados(`produtos/${codigo}`),
    sessao: () => buscarDados('sessao'),
    entrar: (usuario, senha, token) => requisitarApi('login', { metodo: 'POST', dados: { usuario, senha }, token }),
    sair: token => requisitarApi('logout', { metodo: 'POST', token }),
    listarAdministracao: tipo => buscarDados(`administracao/${tipo}`),
    cadastrar: (tipo, dados, token) => requisitarApi(tipo, { metodo: 'POST', dados, token }),
    atualizar: (tipo, codigo, dados, token) => requisitarApi(`${tipo}/${codigo}`, { metodo: 'PUT', dados, token }),
    excluir: (tipo, codigo, token) => requisitarApi(`${tipo}/${codigo}`, { metodo: 'DELETE', token }),
    enviarImagem: (arquivo, token) => {
        const dados = new FormData(); dados.append('imagem', arquivo);
        return requisitarApi('imagens', { metodo: 'POST', dados, token });
    }
};
