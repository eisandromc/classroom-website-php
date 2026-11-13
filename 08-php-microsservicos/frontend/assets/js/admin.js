/* Administração WebShoes: o browser não conecta ao banco, usa a API REST da versão 04. */
(() => {
    'use strict';
    let token = '';
    let tipo = 'produtos';
    let codigoEdicao = 0;
    let registros = [];
    let categorias = [];
    const elemento = id => document.getElementById(id);
    const escapar = valor => String(valor ?? '').replace(/[&<>"']/g, c => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c]));
    const imagem = nome => (String(nome).startsWith('uploads/') ? '../imagens/uploads/' : 'assets/img/') + encodeURIComponent(String(nome || '').split('/').pop());
    const dinheiro = valor => Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    function mensagem(texto, erro = false) {
        elemento('status-admin').innerHTML = texto ? `<div class="${erro?'painel-alerta':'painel-sucesso'}">${escapar(texto)}</div>` : '';
    }
    function exibirAutenticacao(autenticado) {
        elemento('secao-login').hidden = autenticado;
        elemento('secao-admin').hidden = !autenticado;
        elemento('botao-sair').hidden = !autenticado;
    }
    async function verificarSessao() {
        const resposta = await ServicoWebShoes.sessao();
        token = resposta.token;
        exibirAutenticacao(resposta.autenticado);
        if (resposta.autenticado) await carregarRegistros();
    }
    function campo(nome, rotulo, valor = '', tipoInput = 'text', atributos = '') {
        return `<div class="painel-campo"><label for="campo-${nome}">${escapar(rotulo)}</label><input id="campo-${nome}" name="${nome}" type="${tipoInput}" value="${escapar(valor)}" ${atributos}></div>`;
    }
    function blocoTexto(nome,rotulo,valor='') {
        return `<div class="painel-campo largo"><label for="campo-${nome}">${escapar(rotulo)}</label><textarea id="campo-${nome}" name="${nome}" required>${escapar(valor)}</textarea></div>`;
    }
    async function carregarRegistros() {
        elemento('painel-lista').hidden=false;
        elemento('painel-formulario').hidden=true;
        elemento('tabela-registros').textContent = 'Consultando API REST...';
        document.querySelectorAll('[data-tipo]').forEach(a => a.setAttribute('aria-current',a.dataset.tipo===tipo?'page':'false'));
        elemento('titulo-lista').textContent=tipo==='produtos'?'Produtos cadastrados':'Categorias cadastradas';
        elemento('botao-novo').textContent=tipo==='produtos'?'+ Novo produto':'+ Nova categoria';
        try {
            const [resposta, categoriasResposta] = await Promise.all([
                ServicoWebShoes.listarAdministracao(tipo),
                ServicoWebShoes.listarAdministracao('categorias')
            ]);
            registros=resposta.dados; categorias=categoriasResposta.dados;
            if (!registros.length) { elemento('tabela-registros').textContent='Nenhum cadastro encontrado.'; return; }
            elemento('tabela-registros').innerHTML=`<div class="painel-tabela-rolagem"><table><thead><tr>
                <th>ID / Imagem</th><th>Nome</th><th>${tipo==='produtos'?'Preço':'Produtos'}</th><th>Status</th><th>Ações</th></tr></thead>
                <tbody>${registros.map(item=>`<tr>
                <td>${tipo==='produtos'?`<img class="painel-miniatura" src="${imagem(item.imagem)}" alt="Imagem de ${escapar(item.nome)}">`:Number(item.id)}</td>
                <td><strong>${escapar(item.nome)}</strong><div class="painel-dica">${escapar(tipo==='produtos'?item.categoria_nome:item.slug)}</div></td>
                <td>${tipo==='produtos'?dinheiro(item.preco):Number(item.quantidade_produtos)}</td>
                <td>${Number(item.ativo)?'Ativo':'Inativo'}</td>
                <td><div class="painel-botoes"><button type="button" class="painel-botao secundario" data-editar="${Number(item.id)}">Editar</button>
                    <button type="button" class="painel-botao perigo" data-excluir="${Number(item.id)}">Excluir</button></div></td>
                </tr>`).join('')}</tbody></table></div>`;
        } catch(erro) {
            elemento('tabela-registros').textContent='Não foi possível consultar a API.';
            mensagem(erro.message,true);
            if (erro.status===401) exibirAutenticacao(false);
        }
    }
    function abrirFormulario(codigo=0) {
        codigoEdicao=codigo;
        const d = registros.find(r=>Number(r.id)===codigo)||{};
        elemento('painel-lista').hidden=true;
        elemento('painel-formulario').hidden=false;
        elemento('titulo-formulario').textContent=(codigo?'Editar ':'Cadastrar ')+(tipo==='produtos'?'produto':'categoria');
        let html='';
        if (tipo==='categorias') {
            html += campo('nome','Nome da categoria',d.nome,'text','maxlength="80" required');
            html += campo('slug','Slug (opcional)',d.slug,'text','maxlength="100"');
            html += campo('descricao','Descrição',d.descricao,'text','maxlength="255"');
            html += campo('cor','Cor da categoria',d.cor||'#F1F1F1','color','required');
        } else {
            html += campo('nome','Nome do tênis',d.nome,'text','maxlength="120" required');
            html += campo('slug','Slug (opcional)',d.slug,'text','maxlength="150"');
            html += campo('marca','Marca',d.marca,'text','maxlength="70" required');
            html += `<div class="painel-campo"><label for="campo-categoria_id">Categoria</label><select id="campo-categoria_id" name="categoria_id" required>${categorias.map(cat=>`<option value="${Number(cat.id)}" ${Number(cat.id)===Number(d.categoria_id)?'selected':''}>${escapar(cat.nome)}</option>`).join('')}</select></div>`;
            html += campo('preco','Preço (R$)',d.preco??'','number','min="0" step="0.01" required');
            html += campo('preco_anterior','Preço anterior',d.preco_anterior??'','number','min="0" step="0.01"');
            html += campo('descricao_curta','Descrição curta',d.descricao_curta,'text','maxlength="250" required');
            html += campo('etiqueta','Etiqueta',d.etiqueta,'text','maxlength="30"');
            html += campo('cor','Cores do tênis',d.cor,'text','maxlength="80" required');
            html += campo('tamanhos','Numerações (ex.: 36,37,38)',d.tamanhos,'text','maxlength="80" required');
            html += blocoTexto('descricao','Descrição completa',d.descricao);
            html += `<div class="painel-campo largo"><label for="campo-imagem">Imagem JPG, PNG ou WEBP · até 3 MB</label><input type="file" name="imagem" id="campo-imagem" accept="image/jpeg,image/png,image/webp">${d.imagem?`<img class="painel-miniatura" src="${imagem(d.imagem)}" alt="Imagem atual do produto">`:'<p class="painel-dica">Se nenhuma imagem for enviada, será usada uma imagem ilustrativa.</p>'}</div>`;
        }
        elemento('campos-formulario').innerHTML=html;
        elemento('campo-ativo').checked=d.ativo===undefined||Number(d.ativo)===1;
        elemento('campo-destaque').checked=Number(d.destaque)===1;
        elemento('opcao-destaque').hidden=tipo!=='produtos';
        elemento('painel-formulario').scrollIntoView({behavior:'smooth',block:'start'});
    }
    document.querySelectorAll('[data-tipo]').forEach(a=>a.addEventListener('click',async evento=>{
        evento.preventDefault();tipo=a.dataset.tipo;mensagem('');await carregarRegistros();
    }));
    elemento('botao-novo').addEventListener('click',()=>abrirFormulario());
    elemento('botao-cancelar').addEventListener('click',()=>{mensagem('');carregarRegistros();});
    elemento('tabela-registros').addEventListener('click',async evento=>{
        const editar=evento.target.closest('[data-editar]');
        const excluir=evento.target.closest('[data-excluir]');
        if (editar) abrirFormulario(Number(editar.dataset.editar));
        if (excluir && confirm('Excluir definitivamente o cadastro selecionado?')) {
            try { await ServicoWebShoes.excluir(tipo,Number(excluir.dataset.excluir),token); await carregarRegistros(); mensagem('Registro excluído.'); }
            catch(erro){mensagem(erro.message,true);}
        }
    });
    elemento('formulario-login').addEventListener('submit',async evento=>{
        evento.preventDefault();
        const botao=evento.target.querySelector('button[type=submit]');botao.disabled=true;
        try {
            const dados=new FormData(evento.target);
            const resposta=await ServicoWebShoes.entrar(String(dados.get('usuario')),String(dados.get('senha')),token);
            token=resposta.token;evento.target.reset();exibirAutenticacao(true);await carregarRegistros();mensagem('Login efetuado.');
        } catch(erro){mensagem(erro.message,true);} finally{botao.disabled=false;}
    });
    elemento('botao-sair').addEventListener('click',async()=>{
        try { await ServicoWebShoes.sair(token);token='';registros=[];categorias=[];await verificarSessao();mensagem('Sessão encerrada.'); }
        catch(erro){mensagem(erro.message,true);}
    });
    elemento('formulario-registro').addEventListener('submit',async evento=>{
        evento.preventDefault();const botao=elemento('botao-salvar');botao.disabled=true;
        try {
            const dadosFormulario=new FormData(evento.target);
            const dados=Object.fromEntries(dadosFormulario.entries());
            delete dados.imagem;
            dados.ativo=elemento('campo-ativo').checked?1:0;
            if(tipo==='produtos') {
                dados.destaque=elemento('campo-destaque').checked?1:0;
                const arquivo=elemento('campo-imagem').files[0];
                if (arquivo) {
                    if(arquivo.size>3*1024*1024) throw new Error('A imagem deve ter até 3 MB.');
                    const resultado=await ServicoWebShoes.enviarImagem(arquivo,token);
                    dados.imagem=resultado.imagem;
                } else if(codigoEdicao) dados.imagem=registros.find(r=>Number(r.id)===codigoEdicao)?.imagem;
            }
            if (codigoEdicao) await ServicoWebShoes.atualizar(tipo,codigoEdicao,dados,token);
            else await ServicoWebShoes.cadastrar(tipo,dados,token);
            await carregarRegistros();mensagem('Cadastro salvo com sucesso.');
        } catch(erro){mensagem(erro.message,true);if (erro.status===401) exibirAutenticacao(false);}
        finally{botao.disabled=false;}
    });
    verificarSessao().catch(erro=>{exibirAutenticacao(false);mensagem(erro.message,true);});
})();
