<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 23.10.18
 * Time: 23:38
 */

namespace Commands;


abstract class Command
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @param array $params
     * @return mixed
     */
    abstract public function handle(array $params);

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}