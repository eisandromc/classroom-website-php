/* WebShoes — interações de interface compartilhadas pelas 3 vitrines. */
(function () {
    'use strict';
    const CHAVE_SACOLA = 'webshoes_sacola_demo_v1';
    let mensagemTempo;
    const moeda = valor => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(valor) || 0);
    const limparTexto = texto => String(texto ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    function obterSacola() {
        try { const itens = JSON.parse(localStorage.getItem(CHAVE_SACOLA) || '[]'); return Array.isArray(itens) ? itens : []; }
        catch { return []; }
    }
    function salvarSacola(itens) {
        try { localStorage.setItem(CHAVE_SACOLA, JSON.stringify(itens)); } catch { mostrarAviso('Não foi possível salvar sua sacola neste navegador.'); }
        atualizarSacola();
    }
    function mostrarAviso(mensagem) {
        const aviso = document.querySelector('#toast'); if (!aviso) return;
        aviso.textContent = mensagem; aviso.classList.add('visivel');
        clearTimeout(mensagemTempo); mensagemTempo = setTimeout(() => aviso.classList.remove('visivel'), 3300);
    }
    function atualizarSacola() {
        const itens = obterSacola(); const contador = document.querySelector('#contador-sacola');
        if (contador) contador.textContent = String(itens.reduce((t, p) => t + Number(p.quantidade || 1), 0));
        const corpo = document.querySelector('#sacola-corpo'); if (!corpo) return;
        corpo.innerHTML = itens.length ? itens.map((item, i) => `
            <div class="sacola-item">
              <img src="${limparTexto(item.imagem)}" alt="${limparTexto(item.nome)}">
              <div><strong>${limparTexto(item.nome)}</strong><small>Tamanho ${limparTexto(item.tamanho)} · ${Number(item.quantidade||1)} un.</small>
              <small>${moeda(item.preco * Number(item.quantidade||1))}</small>
              <button type="button" data-remover="${i}">Remover da sacola</button></div>
            </div>`).join('') : '<p>Sua sacola está vazia. Explore a coleção e escolha seu próximo par.</p>';
        const total = document.querySelector('#sacola-total'); if (total) total.textContent = moeda(itens.reduce((t, i) => t + i.preco * Number(i.quantidade||1), 0));
    }
    function abrirSacola() {
        document.querySelector('#sacola-cortina')?.classList.add('visivel');
        document.querySelector('#sacola-painel')?.removeAttribute('inert');
        document.querySelector('#sacola-painel')?.classList.add('aberto');
        document.body.classList.add('sem-rolagem');
        document.querySelector('#fechar-sacola')?.focus();
    }
    function fecharSacola() {
        document.querySelector('#sacola-cortina')?.classList.remove('visivel');
        document.querySelector('#sacola-painel')?.classList.remove('aberto');
        document.querySelector('#sacola-painel')?.setAttribute('inert', '');
        document.body.classList.remove('sem-rolagem');
        document.querySelector('#abrir-sacola')?.focus();
    }
    function configurarTamanhos() {
        document.querySelectorAll('[data-tamanho]').forEach(botao => {
            botao.addEventListener('click', () => {
                document.querySelectorAll('[data-tamanho]').forEach(x => { x.classList.remove('selecionado'); x.setAttribute('aria-pressed','false'); });
                botao.classList.add('selecionado'); botao.setAttribute('aria-pressed','true');
            });
        });
    }
    function configurarCompra() {
        const botao = document.querySelector('#adicionar-sacola'); if (!botao || botao.dataset.iniciado) return;
        botao.dataset.iniciado = 'sim';
        botao.addEventListener('click', () => {
            const tamanho = document.querySelector('[data-tamanho].selecionado')?.dataset.tamanho;
            if (!tamanho) { mostrarAviso('Selecione um tamanho antes de adicionar.'); return; }
            const dados = document.querySelector('#dados-produto'); if (!dados) return;
            const produto = { id: Number(dados.dataset.id), nome: dados.dataset.nome, preco: Number(dados.dataset.preco), imagem: dados.dataset.imagem, tamanho, quantidade: 1 };
            const itens = obterSacola(); const existente = itens.find(x => x.id === produto.id && x.tamanho === tamanho);
            if (existente) existente.quantidade = Number(existente.quantidade || 1) + 1; else itens.push(produto);
            salvarSacola(itens); mostrarAviso('Produto adicionado à sacola.'); abrirSacola();
        });
    }
    document.addEventListener('DOMContentLoaded', () => {
        atualizarSacola(); configurarTamanhos(); configurarCompra();
        document.querySelector('#abrir-sacola')?.addEventListener('click', abrirSacola);
        document.querySelector('#fechar-sacola')?.addEventListener('click', fecharSacola);
        document.querySelector('#sacola-cortina')?.addEventListener('click', fecharSacola);
        document.querySelector('#sacola-corpo')?.addEventListener('click', e => {
            const indice = e.target.closest('[data-remover]')?.dataset.remover;
            if (indice === undefined) return;
            const itens = obterSacola(); itens.splice(Number(indice), 1); salvarSacola(itens);
        });
        document.querySelector('#menu-mobile')?.addEventListener('click', () => {
            const nav = document.querySelector('#navegacao'); const aberto = nav?.classList.toggle('aberta');
            document.querySelector('#menu-mobile')?.setAttribute('aria-expanded', String(Boolean(aberto)));
        });
        document.addEventListener('keydown', e => {
            const painel = document.querySelector('#sacola-painel.aberto');
            if (!painel) return;
            if (e.key === 'Escape') { fecharSacola(); return; }
            if (e.key !== 'Tab') return;
            const botoes = [...painel.querySelectorAll('a[href],button:not([disabled]),[tabindex="0"]')];
            if (!botoes.length) return;
            const primeiro = botoes[0], ultimo = botoes[botoes.length - 1];
            if (e.shiftKey && document.activeElement === primeiro) { e.preventDefault(); ultimo.focus(); }
            else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primeiro.focus(); }
        });
    });
    window.WebShoes = { moeda, limparTexto, configurarTamanhos, configurarCompra, mostrarAviso };
})();
