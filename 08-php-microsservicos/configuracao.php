<?php
/** Configuração interna somente para laboratório local; nunca expor o gateway fora do host sem autenticação forte. */
return [
  'catalogo'=>getenv('WEBSHOES_CATALOGO_URL') ?: 'http://127.0.0.1:8114/index.php',
  'identidade'=>getenv('WEBSHOES_IDENTIDADE_URL') ?: 'http://127.0.0.1:8214/index.php',
  'chave'=>getenv('WEBSHOES_CHAVE_INTERNA') ?: 'webshoes-demo-local-altere',
];
