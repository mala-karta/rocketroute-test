<?php
/**
 * User: irene
 * Date: 08.08.2018
 */

require './../defines.php';
require BASE_PATH . '/system.php';
require BASE_PATH . '/vendor/autoload.php';

use RRTest\Config;
use RRTest\Processor;

$config = new Config();

//check if ajax request
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 'XMLHttpRequest' == $_SERVER['HTTP_X_REQUESTED_WITH']) {
    $processor = new Processor($config);
    $processor->process();
    die();
}


$loader = new Twig_Loader_Filesystem(BASE_PATH . '/templates');
$twig = new Twig_Environment($loader);
//display map
echo $twig->render(
    'index.html',
    [
        'BASE_URL'     => BASE_URL,
        'googleApiKey' => $config->getGoogleApiKey(),
    ]
);
