<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 25.10.18
 * Time: 0:48
 */

namespace Jobs;


abstract class Job
{

    /**
     * @param array $params
     * @return mixed
     */
    abstract public function run($params = []);
}