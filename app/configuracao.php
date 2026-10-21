<?php
/*
 * CONFIGURAÇÃO GERAL DO SISTEMA
 * ------------------------------------------------------------------
 * Este arquivo é carregado em TODA requisição (public/index.php).
 * Aqui ficam as constantes usadas pelo sistema inteiro.
 */

// Caminho FÍSICO da pasta /app (usado para localizar controllers, models e views
// de forma segura, sem depender de "../" relativo).
define('APP', dirname(__FILE__));

// URL base do sistema. Se mudar a pasta ou o domínio, altere somente aqui.
define('URL', 'http://localhost/Demandfy');

define('APP_NAME', 'Demandfy');
const APP_VERSAO = '1.0.0';

// Fuso horário do PHP (Guajará-Mirim / Rondônia = UTC-4).
// Usado nas datas exibidas e também enviado ao MySQL (ver Database.php),
// assim o PHP e o banco concordam na hora.
date_default_timezone_set('America/Porto_Velho');

/*
 * DADOS DE CONEXÃO COM O BANCO
 * Podem ser definidos por variável de ambiente (DEMANDFY_DB_*) ou, se não existirem,
 * usam o valor padrão depois do "?:".
 * O XAMPP deste ambiente usa a porta 3307; o banco "demandfy" é criado por
 * database/schema.sql.
 * Se o seu MySQL usa outra porta/banco, altere os valores padrão abaixo.
 */
define('DB_HOST', getenv('DEMANDFY_DB_HOST') ?: '127.0.0.1');
define('DB_USER', getenv('DEMANDFY_DB_USER') ?: 'root');
define('DB_PASS', getenv('DEMANDFY_DB_PASS') ?: '');
define('DB_NAME', getenv('DEMANDFY_DB_NAME') ?: 'demandfy');
define('DB_PORT', getenv('DEMANDFY_DB_PORT') ?: '3307');
