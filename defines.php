<?php
/**
 * User: irene
 * Date: 09.08.2018
 */

$baseUrl = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://')
    . $_SERVER['HTTP_HOST'];
define('BASE_URL', $baseUrl);

define ('WWW_PATH', realpath('.') );
define ('BASE_PATH', WWW_PATH . '/..');
