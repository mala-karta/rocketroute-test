<?php
/**
 * User: irene
 * Date: 08.08.2018
 */

require  './defines.php';
require WWW_PATH . '/system.php';
require BASE_PATH . '/config.php';
require BASE_PATH . '/vendor/autoload.php';

use RRTest\Processor;

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH']) {
    //todo: process ajax
    $processor = new Processor($Config);
    $processor->process();
    die();
}

$loader = new Twig_Loader_Filesystem(BASE_PATH . '/templates');
$twig = new Twig_Environment($loader);

echo $twig->render(
    'index.html',
    [
        'BASE_URL'     => BASE_URL,
        'googleApiKey' => $Config->getGoogleApiKey(),
        'markerImage'  => BASE_URL . '/images/warning-icon-th.png',
    ]
);
