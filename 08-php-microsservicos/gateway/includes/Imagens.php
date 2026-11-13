<?php
/** Upload seguro de imagens para a pasta compartilhada webshoes/imagens/uploads. */
function salvarImagem(?array $arquivo): ?string
{
    if (!$arquivo || (int)($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if (($arquivo['error'] ?? null) !== UPLOAD_ERR_OK) throw new InvalidArgumentException('Falha no envio da imagem.');
    if (!is_uploaded_file((string)$arquivo['tmp_name'])) throw new InvalidArgumentException('Upload inválido.');
    if ((int)$arquivo['size'] <= 0 || (int)$arquivo['size'] > 3 * 1024 * 1024) {
        throw new InvalidArgumentException('Imagem deve ter até 3 MB.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
    $permitidos = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $dimensoes = @getimagesize($arquivo['tmp_name']);
    if (!isset($permitidos[$mime]) || $dimensoes === false || $dimensoes[0] > 4000 || $dimensoes[1] > 4000) {
        throw new InvalidArgumentException('Envie imagem JPG, PNG ou WEBP válida.');
    }
    $pasta = dirname(__DIR__, 2) . '/imagens/uploads';
    if (!is_dir($pasta) || !is_writable($pasta)) {
        throw new RuntimeException('Pasta compartilhada de imagens não existe ou não permite gravação.');
    }
    $nome = bin2hex(random_bytes(16)) . '.' . $permitidos[$mime];
    if (!move_uploaded_file($arquivo['tmp_name'], $pasta . '/' . $nome)) {
        throw new RuntimeException('Não foi possível salvar a imagem.');
    }
    return 'uploads/' . $nome;
}
