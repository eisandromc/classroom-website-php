</main>
<footer class="rodape">
    <div class="conteiner">
        <div class="rodape-miolo">
            <div><a href="<?= escapar($inicio) ?>" class="logo"><span class="logo-marca">W</span><span>WEB<em>SHOES</em></span></a>
                <p>Tênis, cultura e movimento. Projeto didático para comparar arquiteturas PHP e consumo de API REST.</p>
            </div>
            <div class="rodape-links"><div><strong>EXPLORAR</strong><a href="<?= escapar($inicio) ?>#colecao">Coleção</a><a href="<?= escapar($linkCategoria(1)) ?>">Lifestyle</a><a href="<?= escapar($linkCategoria(2)) ?>">Corrida</a></div>
            <div><strong>SOBRE</strong><a href="<?= escapar($inicio) ?>">WebShoes</a><a href="<?= escapar($inicio) ?>#colecao">Produtos</a><a href="#conteudo">Voltar ao topo</a></div></div>
        </div>
        <div class="rodape-base"><span>© WebShoes · Projeto educacional, marcas e produtos fictícios.</span><span>Sem autenticação, pagamento ou processamento real de pedidos.</span></div>
    </div>
</footer>
<div class="sacola-cortina" id="sacola-cortina"></div>
<aside class="sacola-painel" id="sacola-painel" inert role="dialog" aria-modal="true" aria-label="Sacola de compras">
    <div class="sacola-topo"><h2>Sua sacola</h2><button class="botao-icone" type="button" id="fechar-sacola" aria-label="Fechar sacola">×</button></div>
    <div class="sacola-corpo" id="sacola-corpo"></div>
    <div class="sacola-base"><div class="sacola-total"><span>Subtotal</span><span id="sacola-total">R$ 0,00</span></div><button class="botao-primario" type="button" disabled aria-disabled="true">Checkout indisponível</button><p class="sacola-nota">Sacola demonstrativa salva somente neste navegador.</p></div>
</aside>
<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script src="assets/js/loja.js" defer></script>
</body>
</html>
