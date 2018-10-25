<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 23.10.18
 * Time: 21:18
 */

namespace Classes;


use Exception;

class EnvConfig
{

    /**
     * @var null | EnvConfig
     */
    static private $instance = null;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * EnvConfig constructor.
     */
    protected function __construct()
    {
        if (! file_exists('.env')) {
            Log::error(new Exception('.env file not found!'));
            die();
        }

        $this->config = parse_ini_file('.env');
    }

    /**
     * @return EnvConfig|null
     */
    static public function getConfig()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function get(string $name)
    {
        return $this->config[$name];
    }
}