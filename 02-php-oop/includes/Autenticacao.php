<?php
/** Autenticação de sessão e prevenção básica de CSRF. */
function iniciarSessaoAdmin(string $nome): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name($nome);
        session_set_cookie_params([
            'lifetime' => 0, 'path' => '/', 'httponly' => true,
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}
function tokenFormulario(): string
{
    if (empty($_SESSION['token_formulario'])) $_SESSION['token_formulario'] = bin2hex(random_bytes(24));
    return $_SESSION['token_formulario'];
}
function verificarToken(?string $token): void
{
    if (!$token || !hash_equals(tokenFormulario(), $token)) {
        throw new RuntimeException('Sessão expirada ou token de segurança inválido.');
    }
}
function administradorAutenticado(): bool
{
    return isset($_SESSION['administrador_id']) && (int)$_SESSION['administrador_id'] > 0;
}
function entrarAdministrador(int $codigo): void
{
    session_regenerate_id(true);
    $_SESSION['administrador_id'] = $codigo;
    $_SESSION['tentativas_login'] = 0;
    $_SESSION['token_formulario'] = bin2hex(random_bytes(24));
}
function sairAdministrador(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
function permitirTentativaLogin(): void
{
    if ((int)($_SESSION['bloqueio_login_ate'] ?? 0) > time()) {
        throw new RuntimeException('Muitas tentativas. Tente novamente em alguns minutos.');
    }
}
function registrarFalhaLogin(): void
{
    $_SESSION['tentativas_login'] = (int)($_SESSION['tentativas_login'] ?? 0) + 1;
    if ($_SESSION['tentativas_login'] >= 8) {
        $_SESSION['bloqueio_login_ate'] = time() + 300;
        $_SESSION['tentativas_login'] = 0;
    }
}
