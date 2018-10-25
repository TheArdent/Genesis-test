<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 1:36
 */

namespace Commands;


use Classes\EnvConfig;
use Classes\Log;
use VK\VK;
use VK\VKException;

class AuthorizeVKCommand extends Command
{

    protected $name = 'vk:auth';

    protected $description = 'Authorize vk command';

    /**
     * @param array $params
     * @return mixed|void
     */
    public function handle(array $params)
    {
        $config = EnvConfig::getConfig();

        try {
            $vk = new VK($config->get('VK_API_KEY'), $config->get('VK_API_SECRET'));
        } catch (\Exception $exception) {
            Log::error($exception);
            die();
        }

        $authorize_url = $vk->getAuthorizeURL('offline,photos', $config->get('VK_API_CALLBACK'));

        echo 'Auth url:'.$authorize_url.PHP_EOL;
        echo 'Enter the link to which you were redirected:';

        $url = readline();
        $code = str_replace($config->get('VK_API_CALLBACK').'?code=', '', $url);

        try {
            $access_token = $vk->getAccessToken($code, $config->get('VK_API_CALLBACK'));
        } catch (VKException $e) {
            Log::error($e);
            die();
        }

        file_put_contents('.tmp', $access_token);

        echo 'Access token successful generated!'.PHP_EOL;
    }
}