<?php

use React\Socket\Server;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;

require "vendor/autoload.php";
require "ConnectionsPool.php";

$loop = Factory::create();

$server = new Server('127.0.0.1:8000', $loop);

$pool = new ConnectionsPool();

$server->on('connection', function (ConnectionInterface $connection) use ($pool) {
    $pool->add($connection);
});

$loop->run();
