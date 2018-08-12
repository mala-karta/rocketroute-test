<?php
/**
 * Created by PhpStorm.
 * User: irene
 * Date: 08.08.2018
 */

function vdie()
{
    header('Content-Type: text/html; charset=utf-8');
    $backtrace = debug_backtrace();
    ob_start();
    echo $backtrace[0]['file'], ' (', $backtrace[0]['line'], '):<br />' . PHP_EOL . PHP_EOL;
    $vars = func_get_args();
    foreach ($vars as $var) {
        echo '<pre>';
        if (is_scalar($var) || is_null($var)) {
            var_dump($var);
        } else {
            echo PHP_EOL;
            print_r($var);
            echo PHP_EOL;
        }
        echo '</pre>' . PHP_EOL;
    }
    echo PHP_EOL . '<hr />BACKTRACE<hr />' . PHP_EOL;
    array_shift($backtrace);

    function _removeObjects(&$array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                _removeObjects($value);
            } elseif (is_object($value)) {
                unset($array[$key]);
                $array[$key] = get_class($value) . ' Object';
            }
        }
    }

    _removeObjects($backtrace);
    echo PHP_EOL . '<pre>' . PHP_EOL;
    print_r($backtrace);
    echo PHP_EOL . '</pre>' . PHP_EOL;
    $html = ob_get_contents();
    ob_end_clean();
    if ('XMLHttpRequest' == __getHttpHeader('X_REQUESTED_WITH')) {
        $html = str_replace(array('<br />', '<pre>', '</pre>'), array("\n", "\n", ''), $html);
        $html = str_replace(array('<hr />'), "\n" . str_repeat('-', 80) . "\n", $html);
    }
    echo str_replace("\n</pre>", '</pre>', $html);
    exit();
}


function __getHttpHeader($header)
{
    if (empty($header)) {
        return false;
    }
    // Try to get it from the $_SERVER array first
    $temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
    if (!empty($_SERVER[$temp])) {
        return $_SERVER[$temp];
    }
    // This seems to be the only way to get the Authorization header on
    // Apache
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (!empty($headers[$header])) {
            return $headers[$header];
        }
    }
    return false;
}