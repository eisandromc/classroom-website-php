/* Telas da vitrine WebShoes: apenas renderização e eventos, sem SQL ou PHP. */
(function () {
    'use strict';
    const { limparTexto: escapar, moeda } = window.WebShoes;
    const parametros = new URLSearchParams(location.search);
    const codigoCategoria = Number(parametros.get('id'));
    const imagem = nome => (String(nome || '').startsWith('uploads/') ? '../imagens/uploads/' : 'assets/img/') + encodeURIComponent(String(nome || '').split('/').pop());
    const erroHtml = mensagem => `<div class="vazio"><h3>Não foi possível carregar esta seção.</h3><p>${escapar(mensagem)}</p><a class="link-simples" href="index.html">Voltar ao início</a></div>`;
    const card = p => `<article class="card-produto">
        <a class="card-imagem" href="produto.html?id=${Number(p.id)}" aria-label="Ver detalhes de ${escapar(p.nome)}">
          ${p.etiqueta ? `<span class="tag">${escapar(p.etiqueta)}</span>` : ''}
          <img src="${imagem(p.imagem)}" alt="Tênis ${escapar(p.nome)}" loading="lazy">
        </a><div class="card-info"><div class="card-sobretitulo">${escapar(p.marca)} · ${escapar(p.categoria_nome)}</div>
        <h3 class="card-titulo"><a href="produto.html?id=${Number(p.id)}">${escapar(p.nome)}</a></h3>
        <div class="card-rodape"><span class="preco">${moeda(p.preco)}</span>${p.preco_anterior != null ? `<span class="preco-antigo">${moeda(p.preco_anterior)}</span>` : ''}</div>
        </div></article>`;
    async function filtros() {
        const resultado = await ServicoWebShoes.listarCategorias();
        const conteiner = document.querySelector('#filtros');
        if (!conteiner) return resultado.dados;
        conteiner.innerHTML = '<a class="filtro'+ (!codigoCategoria?' ativo':'') +'" href="index.html#colecao">Todos os modelos</a>' +
            resultado.dados.map(c => `<a class="filtro${codigoCategoria===Number(c.id)?' ativo':''}" href="categoria.html?id=${Number(c.id)}">${escapar(c.nome)}</a>`).join('');
        return resultado.dados;
    }
    async function carregarGrade() {
        const grade = document.querySelector('#grade-produtos'); if (!grade) return;
        grade.innerHTML = '<div class="carregando">Consultando a API...</div>';
        const consulta = { categoria: codigoCategoria || null, busca: document.querySelector('#busca')?.value.trim(), ordem: document.querySelector('#ordem')?.value || 'destaques' };
        try {
            const retorno = await ServicoWebShoes.listarProdutos(consulta);
            grade.innerHTML = retorno.dados.length ? retorno.dados.map(card).join('') : '<div class="vazio"><h3>Nenhum produto encontrado.</h3><p>Tente outra categoria ou busca.</p></div>';
        } catch (e) { grade.innerHTML = erroHtml(e.message); }
    }
    async function iniciarCatalogo() {
        const grade = document.querySelector('#grade-produtos'); if (!grade) return;
        try {
            const categorias = await filtros();
            if (location.pathname.endsWith('categoria.html')) {
                const atual = categorias.find(c => Number(c.id) === codigoCategoria);
                if (atual) {
                    document.querySelector('#nome-categoria').textContent = atual.nome;
                    document.querySelector('#descricao-categoria').textContent = atual.descricao;
                    document.title = `${atual.nome} | WebShoes`;
                } else {
                    document.querySelector('#nome-categoria').textContent = 'Categoria não encontrada';
                    document.querySelector('#descricao-categoria').textContent = 'Volte à coleção e escolha uma categoria válida.';
                    grade.innerHTML = erroHtml('Categoria não encontrada.'); return;
                }
            }
        } catch (e) { grade.innerHTML = erroHtml(e.message); return; }
        document.querySelector('#formulario-busca')?.addEventListener('submit', e => { e.preventDefault(); carregarGrade(); });
        document.querySelector('#ordem')?.addEventListener('change', carregarGrade);
        await carregarGrade();
    }
    async function iniciarDetalhe() {
        const conteiner = document.querySelector('#detalhe-produto'); if (!conteiner) return;
        const codigo = Number(parametros.get('id'));
        if (!Number.isInteger(codigo) || codigo < 1) { conteiner.innerHTML = erroHtml('Código inválido.'); return; }
        try {
            const { dados: p } = await ServicoWebShoes.buscarProduto(codigo);
            document.title = `${p.nome} | WebShoes`;
            document.querySelector('#trilha-produto').innerHTML = `<a href="index.html">Início</a><span>/</span><a href="categoria.html?id=${Number(p.categoria_id)}">${escapar(p.categoria_nome)}</a><span>/</span><span>${escapar(p.nome)}</span>`;
            conteiner.innerHTML = `<div class="detalhe-grid">
                <div class="detalhe-arte"><img src="${imagem(p.imagem)}" alt="Tênis ${escapar(p.nome)}">${p.etiqueta ? `<span class="tag detalhe-art-tag">${escapar(p.etiqueta)}</span>` : ''}</div>
                <div class="detalhe-info"><span class="marca">${escapar(p.marca)} / ${escapar(p.categoria_nome)}</span><h1>${escapar(p.nome)}</h1><p class="descricao-curta">${escapar(p.descricao_curta)}</p>
                <div class="preco-detalhe"><span class="preco">${moeda(p.preco)}</span>${p.preco_anterior!=null?`<span class="preco-antigo">${moeda(p.preco_anterior)}</span>`:''}</div>
                <div class="linha"></div><div class="rotulo-detalhe">Cor <span>${escapar(p.cor)}</span></div>
                <div class="rotulo-detalhe" style="margin-top:27px">Selecione o tamanho <span>Numeração BR</span></div>
                <div class="tamanhos" role="group" aria-label="Escolha o tamanho">${String(p.tamanhos).split(',').map(t=>`<button type="button" class="tamanho" data-tamanho="${escapar(t.trim())}" aria-pressed="false">${escapar(t.trim())}</button>`).join('')}</div>
                <div id="dados-produto"></div><button class="botao-primario comprar" type="button" id="adicionar-sacola">Adicionar à sacola <span aria-hidden="true">↗</span></button>
                <div class="linha"></div><p class="descr-longa">${escapar(p.descricao)}</p>
                <div class="beneficios"><div><svg viewBox="0 0 24 24"><path d="m4 12 5 5L20 6"/></svg> Produto fictício para demonstração</div><div><svg viewBox="0 0 24 24"><path d="m4 12 5 5L20 6"/></svg> Sacola local, sem finalização de compra</div></div>
                </div></div>`;
            const dados = conteiner.querySelector('#dados-produto');
            // dataset codificado pela DOM API, nunca por interpolação HTML sem escape.
            dados.dataset.id = String(p.id); dados.dataset.nome = p.nome; dados.dataset.preco = String(p.preco); dados.dataset.imagem = imagem(p.imagem);
            window.WebShoes.configurarTamanhos(); window.WebShoes.configurarCompra();
        } catch (e) { conteiner.innerHTML = erroHtml(e.message); }
    }
    document.addEventListener('DOMContentLoaded', () => { iniciarCatalogo(); iniciarDetalhe(); });
})();
