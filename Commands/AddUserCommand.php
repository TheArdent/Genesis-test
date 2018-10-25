<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 24.10.18
 * Time: 0:00
 */

namespace Commands;


use Classes\EnvConfig;
use Jobs\ParseUserJob;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AddUserCommand extends Command
{

    protected $name = 'user:add';

    protected $description = 'Command for add single user(async) | php index user:add {id}|{filename}';

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

        if (! is_numeric($params[0])) {
            $filename = $params[0];

            if (! file_exists($filename)) {
                echo 'File not exist!'.PHP_EOL;
                die();
            }

            if (pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
                echo 'Invalid file type! Only csv supported!'.PHP_EOL;
                die();
            }

            $msg = new AMQPMessage();

            $row = 1;
            if (($handle = fopen($filename, "r")) !== false) {
                while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                    $msg->setBody($data[0]);
                    $channel->basic_publish($msg, '', 'job_queue');

                    $row++;
                }
                fclose($handle);
            }
        } else {
            $msg = new AMQPMessage(json_encode([
                'class'  => ParseUserJob::class,
                'params' => $params
            ]));

            $channel->basic_publish($msg, '', 'job_queue');
        }

        echo 'Successful add to queue!'.PHP_EOL;

        $channel->close();
        $connection->close();
    }
}