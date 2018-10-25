<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 25.10.18
 * Time: 1:02
 */

namespace Commands;


use Providers\CommandProvider;

class HelpCommand extends Command
{

    protected $name = 'help';

    protected $description = 'Get help command info';

    /**
     * @param array $params
     * @return mixed|void
     */
    public function handle(array $params)
    {
        if (! empty($params)) {
            $command = CommandProvider::getCommand($params[0]);

            if (! $command) {
                echo 'Command not found! More info at README.md'.PHP_EOL;
                die();
            }

            echo $command->getDescription().PHP_EOL;
        } else {
            foreach (CommandProvider::$commands as $command) {
                /** @var Command $entity */
                $entity = new $command;
                echo "\e[0;39m \e[42m".$entity->getName()."\e[0;39m\e[40m | ".$entity->getDescription().PHP_EOL;
            }
            echo 'More info at README.md'.PHP_EOL;
        }
    }
}