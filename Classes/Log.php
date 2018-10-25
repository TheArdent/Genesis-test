<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 25.10.18
 * Time: 1:48
 */

namespace Classes;


class Log
{

    protected function __construct()
    {
    }

    /**
     * @param string $message
     */
    static public function info(string $message)
    {
        self::wroteToFile('Info', $message);
    }

    /**
     * @param \Exception $e
     */
    static public function error(\Exception $e)
    {
        self::wroteToFile('Error', $e->getMessage().PHP_EOL.$e->getFile().':'.$e->getLine());
    }

    /**
     * @param string $type
     * @param string $message
     */
    static protected function wroteToFile(string $type, string $message)
    {
        $msg = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), $type, $message);
        file_put_contents('log.log', $msg, FILE_APPEND);
        echo $msg;
    }
}