<?php
/** Versão 05: funções mysqli_* PROCEDURAIS, sem classes de acesso. */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
function conectarBanco(): mysqli
{
    static $conexao = null;
    if (!$conexao instanceof mysqli) {
        $conexao = mysqli_connect('127.0.0.1', 'root', '', 'webshoes');
        mysqli_set_charset($conexao, 'utf8mb4');
    }
    return $conexao;
}
