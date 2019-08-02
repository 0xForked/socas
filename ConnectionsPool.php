<?php

use React\Socket\ConnectionInterface;

class ConnectionsPool
{

    protected $connections;

    public function __construct()
    {
        $this->connections = new SplObjectStorage();
    }

    public function add(ConnectionInterface $connection)
    {
        $connection->write("Welcome to the jungle! \n");
        $connection->write("Please enter your name: ");
        $this->setUserName($connection, '');
        $this->initEvents($connection);
    }

    private function initEvents(ConnectionInterface $connection)
    {
        $connection->on('data', function ($data) use ($connection) {
            $user = $this->getUserName($connection);
            if (empty($user)) {
                $this->user($connection, $data);
                return;
            }

            $this->pushMessage("$user: $data", $connection);
        });

        $connection->on('close', function () use ($connection) {
            $user = $this->getUserName($connection);
            $this->connections->offsetUnset($connection);
            $this->pushMessage("A user $user leaves the chat \n", $connection);
        });
    }

    private function user(ConnectionInterface $connection, $name)
    {
        $user = str_replace(["\n", "\r"], '', $name);
        $this->setUserName($connection, $user);
        $this->pushMessage("User $user join the chat \n", $connection);
    }

    private function getUserName(ConnectionInterface $connection)
    {
        return $this->connections->offsetGet($connection);
    }

    private function setUserName(ConnectionInterface $connection, $name)
    {
        return $this->connections->offsetSet($connection, $name);
    }

    private function pushMessage($message, ConnectionInterface $except)
    {
        foreach ($this->connections as $connection) {
            if ($connection === $except) {
                $connection->write($message);
            }
        }
    }

}
