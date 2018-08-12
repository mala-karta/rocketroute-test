<?php
/**
 * User: irene
 * Date: 11.08.2018
 *
 * Description: class for processing exceptions
 */

namespace RRTest;

class ExceptionHandler
{
    const ERROR_STATUS = 'error';

    /**
     * display error in json
     *
     * @param $message
     */
    public static function printJsonError($message)
    {
        echo json_encode([
            'status'  => self::ERROR_STATUS,
            'message' => $message,
        ]);
    }

    /**
     * display error in json and die
     *
     * @param $message
     */
    public static function dieJsonError($message)
    {
        self::printJsonError($message);
        die();
    }
}