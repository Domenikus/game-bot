<?php


namespace App\Listeners;


use TeamSpeak3_Node_Server;

abstract class AbstractListener
{
    /**
     * @var TeamSpeak3_Node_Server
     */
    protected $server;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * AbstractListener constructor.
     * @param TeamSpeak3_Node_Server $server
     * @param callable $callback
     */
    public function __construct(TeamSpeak3_Node_Server $server, callable $callback)
    {
        $this->server = $server;
        $this->callback = $callback;
    }

    abstract function init();
}
