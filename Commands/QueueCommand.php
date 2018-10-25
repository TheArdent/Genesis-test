<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 0:44
 */

namespace Commands;


use Classes\EnvConfig;
use Classes\Log;
use Jobs\Job;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class QueueCommand extends Command
{

    protected $name = 'queue';

    protected $description = 'Start queue and waiting for jobs';

    /**
     * @param array $params
     * @return mixed|void
     */
    public function handle(array $params)
    {
        $config = EnvConfig::getConfig();

        $connection = new AMQPStreamConnection($config->get('RABBITMQ_HOST'), $config->get('RABBITMQ_PORT'),
            $config->get('RABBITMQ_USER'), $config->get('RABBITMQ_PASSWORD'));

        $channel = $connection->channel();

        $channel->queue_declare('job_queue');

        Log::info('[*] Waiting for jobs');

        $handler = function (AMQPMessage $msg) {
            $jobData = json_decode($msg->body);
            Log::info('[*] Processing job '.$jobData->class);

            try {
                $job = new $jobData->class;
                if (! $job instanceof Job) {
                    Log::info('[*] Invalid job!');

                    return;
                }
                $job->run($jobData->params);

                Log::info('[*] Job successful finished!');
            } catch (\Exception $e) {
                Log::error($e);
            }
        };

        $channel->basic_consume('job_queue', '', false, false, false, false, $handler);

        while (count($channel->callbacks)) {
            try {
                $channel->wait();
            } catch (\Exception $e) {
                Log::error($e);
            }
        }

        $channel->close();
        $connection->close();
    }
}