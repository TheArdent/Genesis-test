<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 21:49
 */

namespace Commands;


use Classes\DB;
use Classes\Log;
use Console_Table;
use Exception;

class GetInfoCommand extends Command
{

    protected $name = 'info';

    protected $description = 'Get info about parsed user | info {id?}';

    /**
     * @param array $params
     * @return mixed|void
     */
    public function handle(array $params)
    {
        $db    = DB::getInstance();
        $table = new Console_Table();

        if (empty($params)) {
            try {
                $result = $db->select('SELECT vk_user_id,vk_album_id,
                          albums.title          as title,
                          count(photos.user_id) as photos,
                          albums.created        as created
                        FROM users
                          JOIN albums ON users.id = albums.user_id
                          JOIN photos ON albums.id = photos.album_id
                        GROUP BY photos.album_id ORDER BY users.id');
            } catch (Exception $e) {
                Log::error($e);
                die();
            }

            $table->setHeaders(
                array('UserId', 'AlbumId', 'Album name', 'Photos', 'Created')
            );

            foreach ($result as $item) {
                $table->addRow(array_intersect_key($item, array_flip([
                    'vk_user_id',
                    'vk_album_id',
                    'title',
                    'photos',
                    'created'
                ])));
            }
        } else {
            if (! is_numeric($params[0])) {
                echo 'Invalid user ID!'.PHP_EOL;
                die();
            }

            $user = $db->select('SELECT * FROM users WHERE vk_user_id = ? LIMIT 1', $params);

            if (empty($user)) {
                echo 'User not parsed jet!'.PHP_EOL;
                die();
            }

            try {
                $photos = $db->select('SELECT
                      vk_album_id,title,vk_photo_id,link,photos.created as created
                    FROM users
                      JOIN albums ON users.id = albums.user_id
                      JOIN photos ON albums.id = photos.album_id
                    WHERE users.vk_user_id = ? ORDER BY albums.created', $params);
            } catch (Exception $e) {
                Log::error($e);
                die();
            }

            echo 'Info about user #'.$params[0].PHP_EOL;

            $table->setHeaders(
                array('Album ID', 'Title', 'Photo ID', 'URL', 'Created')
            );

            foreach ($photos as $photo) {
                $table->addRow(array_intersect_key($photo, array_flip([
                    'vk_album_id',
                    'title',
                    'vk_photo_id',
                    'link',
                    'created'
                ])));
            }
        }

        echo $table->getTable();
    }
}