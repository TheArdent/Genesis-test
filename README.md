# Genesis test task

## Installation
1) Copy .env.example to .env
2) Setup .env your settings
3) Run 'composer install'
4) Import DB structure from _'dump.sql'_
5) Authorize at VK API - run 'php index vk:auth'

##### If you don`t use Docker run - _'php index queue'_

## Commands
**vk:auth** - Authorize at VK API

**user:add {id}** - Processing user with {id}

**user:add {filename.csv}** - Processing users, export from csv file

**info {id?}** - Get information about processed users, user {id} optional

**help {command}** - Get command description

**queue** - Start daemon for processing of jobs