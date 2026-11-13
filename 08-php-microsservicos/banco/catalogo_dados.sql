-- WEBSHOES | Dados fictícios para demonstração didática
USE webshoes_catalogo;

INSERT INTO categorias (id, nome, slug, descricao, cor, ativo) VALUES
    (1, 'Lifestyle', 'lifestyle', 'Para transformar qualquer rua em passarela.', '#E9E8DD', 1),
    (2, 'Corrida', 'corrida', 'Leveza e movimento em cada quilômetro.', '#E3E8DB', 1),
    (3, 'Basquete', 'basquete', 'Presença dentro e fora das quadras.', '#EFE2D9', 1),
    (4, 'Skate', 'skate', 'Atitude, aderência e estilo urbano.', '#E0E4E9', 1);

INSERT INTO produtos (id, categoria_id, nome, slug, marca, descricao_curta, descricao, preco, preco_anterior, imagem, etiqueta, cor, tamanhos, destaque, ativo) VALUES
    (1, 1, 'Vertex Street One', 'vertex-street-one', 'VERTEX', 'Tênis urbano com visual minimalista e versátil.', 'O Street One mistura linhas limpas, conforto no dia a dia e uma silhueta que combina com qualquer look.', 349.9, 419.9, 'tenis-01.svg', 'NOVO', 'Areia / Coral', '35,36,37,38,39,40,41,42,43', 1, 1),
    (2, 2, 'Aero Pulse Runner', 'aero-pulse-runner', 'AERO', 'Leveza para seguir no seu ritmo.', 'Com estrutura respirável e amortecimento macio, o Pulse Runner foi pensado para treinos e caminhadas.', 499.9, 579.9, 'tenis-02.svg', 'DESTAQUE', 'Verde / Lima', '36,37,38,39,40,41,42,43,44', 1, 1),
    (3, 3, 'Orbit Court High', 'orbit-court-high', 'ORBIT', 'Clássico de quadra com personalidade.', 'O Court High combina suporte no tornozelo e um visual marcante para quem vive o basquete como cultura.', 629.9, NULL, 'tenis-03.svg', 'DROP', 'Vinho / Creme', '37,38,39,40,41,42,43,44', 1, 1),
    (4, 4, 'Kinetik Flat Pro', 'kinetik-flat-pro', 'KINETIK', 'Resistência e liberdade para o rolê.', 'Solado aderente, cabedal reforçado e perfil baixo: um tênis criado para acompanhar os movimentos.', 389.9, 459.9, 'tenis-04.svg', 'NOVO', 'Azul / Cinza', '35,36,37,38,39,40,41,42,43', 0, 1),
    (5, 1, 'Nova Daily 02', 'nova-daily-02', 'NOVA', 'O essencial, elevado a outro nível.', 'Perfil contemporâneo e materiais confortáveis fazem do Daily 02 uma escolha versátil.', 419.9, NULL, 'tenis-05.svg', '', 'Off-white / Preto', '35,36,37,38,39,40,41,42', 1, 1),
    (6, 2, 'Flux Motion X', 'flux-motion-x', 'FLUX', 'Design responsivo para correr mais longe.', 'Construção leve, calce estável e detalhes dinâmicos acompanham sua rotina ativa.', 549.9, 619.9, 'tenis-06.svg', 'DESTAQUE', 'Laranja / Grafite', '36,37,38,39,40,41,42,43,44', 1, 1),
    (7, 3, 'Vertex Jump 90', 'vertex-jump-90', 'VERTEX', 'Energia para entrar em jogo.', 'Silhueta inspirada em clássicos da quadra, com conforto de sobra para o cotidiano.', 699.9, NULL, 'tenis-07.svg', 'DROP', 'Preto / Dourado', '37,38,39,40,41,42,43,44', 0, 1),
    (8, 4, 'Orbit Board Classic', 'orbit-board-classic', 'ORBIT', 'A rua é o seu território.', 'Tração, estrutura e um visual retrô para quem se movimenta sem roteiro.', 329.9, 389.9, 'tenis-08.svg', '', 'Lilás / Marfim', '35,36,37,38,39,40,41,42', 0, 1),
    (9, 1, 'Aero City Form', 'aero-city-form', 'AERO', 'Minimalismo com um toque futurista.', 'Uma proposta de visual urbano com paleta delicada e conforto para todos os dias.', 459.9, 529.9, 'tenis-09.svg', 'NOVO', 'Rosa / Gelo', '35,36,37,38,39,40,41,42', 0, 1),
    (10, 2, 'Kinetik Tempo 3', 'kinetik-tempo-3', 'KINETIK', 'O próximo passo é sempre seu.', 'Malha ventilada, estrutura estável e solado confortável para acompanhar sua rotina.', 589.9, NULL, 'tenis-10.svg', '', 'Azul / Neon', '36,37,38,39,40,41,42,43,44', 0, 1),
    (11, 3, 'Flux Dunk Retro', 'flux-dunk-retro', 'FLUX', 'A cultura da quadra em cada detalhe.', 'Um tênis de presença com cores contrastantes e acabamento clássico.', 729.9, 809.9, 'tenis-11.svg', 'DESTAQUE', 'Verde / Creme', '37,38,39,40,41,42,43,44', 0, 1),
    (12, 4, 'Nova Sidewalk 01', 'nova-sidewalk-01', 'NOVA', 'Feito para acompanhar o seu caminho.', 'Estilo atemporal e sola robusta para os dias que começam e terminam na rua.', 379.9, NULL, 'tenis-12.svg', '', 'Cinza / Coral', '35,36,37,38,39,40,41,42,43', 0, 1);

