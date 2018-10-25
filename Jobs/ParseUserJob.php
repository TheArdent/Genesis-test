<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 0:44
 */

namespace Jobs;

use Classes\EnvConfig;
use Classes\Log;
use Exception;
use Models\Album;
use Models\Photo;
use Models\User;
use VK\VK;
use VK\VKException;

class ParseUserJob extends Job
{

    /**
     * @var VK
     */
    protected $vk;

    /**
     * @param array $params
     * @return mixed|void
     */
    public function run($params = [])
    {
        $config = EnvConfig::getConfig();

        $access_token = $this->getAccessToken();

        try {
            $this->vk = new VK($config->get('VK_API_KEY'), $config->get('VK_API_SECRET'), $access_token);
        } catch (VKException $e) {
            Log::error($e);
            return;
        }

        $user = User::firstOrCreate(['vk_user_id' => $params[0]]);

        $albums = $this->getAlbums($params[0], $user);

        /** @var Album $album */
        foreach ($albums as $album) {
            $this->getPhotosFromAlbum($params[0], $album);
            usleep(300);//Anti-ban,VK API ban for more then 3req/sec
        }
    }

    /**
     * @param int $vkUserId
     * @param User $user
     * @return array
     */
    protected function getAlbums(int $vkUserId, User $user)
    {
        $response = $this->vk->api('photos.getAlbums', [
            'owner_id'    => $vkUserId,
            'version'     => 5.87,
            'need_system' => 0
        ]);

        if (array_key_exists('error', $response) || count($response['response']) <= 1) {
            return [];
        }

        $albums = array_filter($response['response'], 'is_array');

        return array_map(function ($albumData) use ($user) {
            $album = Album::firstOrCreate([
                'user_id'     => $user->id,
                'vk_album_id' => $albumData['aid'],
                'title'       => $albumData['title']
            ]);

            $album->created = date('Y-m-d H:i:s', $albumData['created']);
            $album->save();

            return $album;
        }, $albums);
    }

    /**
     * @param int $vkUserId
     * @param Album $album
     * @return array
     */
    protected function getPhotosFromAlbum(int $vkUserId, Album $album)
    {
        $response = $this->vk->api('photos.get', [
            'owner_id' => $vkUserId,
            'album_id' => $album->vk_album_id,
            'version'  => 5.87,
            'extended' => 0
        ]);

        if (array_key_exists('error', $response) || count($response['response']) <= 1) {
            return [];
        }

        $photos = array_filter($response['response'], 'is_array');

        return array_map(function ($photoData) use ($vkUserId, $album) {
            $photo          = Photo::firstOrCreate([
                'user_id'     => $album->user_id,
                'album_id'    => $album->id,
                'vk_photo_id' => $photoData['pid'],
                'link'        => $photoData['src_xxxbig'] ?? $photoData['src']
            ]);
            $photo->created = date('Y-m-d H:i:s', $photoData['created']);
            $photo->save();

            return $photo;
        }, $photos);
    }

    /**
     * @return string
     */
    protected function getAccessToken()
    {
        try {
            $token_data = json_decode(file_get_contents('.tmp'), true);

            return $token_data['access_token'];
        } catch (Exception $e) {
            Log::error($e);
            return;
        }
    }
}