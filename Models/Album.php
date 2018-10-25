<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 16:22
 */

namespace Models;


use Classes\Entity;

class Album extends Entity
{

    protected $table = 'albums';

    protected $columns = [
        'id',
        'vk_album_id',
        'user_id',
        'title',
        'created'
    ];
}