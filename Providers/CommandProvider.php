<?php
/**
 * Created by PhpStorm.
 * User: theardent
 * Date: 23.10.18
 * Time: 23:45
 */

namespace Providers;


use Commands\AddUserCommand;
use Commands\AuthorizeVKCommand;
use Commands\Command;
use Commands\GetInfoCommand;
use Commands\HelpCommand;
use Commands\QueueCommand;

class CommandProvider
{

    /**
     * @var array
     */
    static public $commands = [
        AddUserCommand::class,
        AuthorizeVKCommand::class,
        QueueCommand::class,
        GetInfoCommand::class,
        HelpCommand::class
    ];

    /**
     * @param string $name
     * @return Command|null
     */
    static public function getCommand(string $name)
    {
        foreach (self::$commands as $command) {
            /** @var Command $commandInstance */
            $commandInstance = new $command;
            if (strcasecmp($name, $commandInstance->getName()) === 0 && $commandInstance instanceof Command) {
                return $commandInstance;
            }
        }

        return null;
    }
}