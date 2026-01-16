<?php

/**
 * Funções comuns de ajuda para o sistema.
 */

// function url(string $path = null): string
// {
//     return CONF_URL_BASE . $path;
// }

if (!function_exists("url")) {
    function url(string $path = null): string
    {
        if (!defined("CONF_URL_BASE")) {
            throw new Exception("A constante CONF_URL_BASE não está definida. Verifique se o Config.php foi carregado.");
        }
        return CONF_URL_BASE . ($path ?? "");
    }
}