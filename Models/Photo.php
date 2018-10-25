<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 17:19
 */

namespace Models;


use Classes\Entity;

class Photo extends Entity
{

    protected $table = 'photos';

    protected $columns = [
        'id',
        'user_id',
        'album_id',
        'vk_photo_id',
        'link',
        'created'
    ];
}