<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 16:22
 */

namespace Models;


use Classes\DB;
use Classes\Entity;
use Classes\Log;
use Exception;

class User extends Entity
{

    protected $table = 'users';

    protected $columns = [
        'id',
        'vk_user_id',
        'created'
    ];

    public function photos()
    {
        $db = DB::getInstance();

        try {
            $result = $db->select('SELECT * FROM photos WHERE user_id = ?', [$this->id]);
        } catch (Exception $e) {
            Log::error($e);
            die();
        }

        return array_map(function ($photo) {
            return new Photo($photo);
        }, $result);
    }
}