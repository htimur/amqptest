<?php

namespace Tirendo\Provider;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPSocketConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class Provider
 * @package Tirendo\AMQP
 */
final class PhpSocketAMQP
{
    /**
     * @var \AMQPConnection
     */
    private $connection;
    /**
     * @var \AMQPChannel
     */
    private $channel;
    /**
     * @var \AMQPExchange
     */
    private $exchange;

    /**
     * @var \AMQPQueue[]
     */
    private $queues;

    /**
     * @param array $connectionConfig
     * @param array $exchangeConfig
     * @param array $queues
     */
    function __construct(array $connectionConfig, array $exchangeConfig, array $queues)
    {
        $this->connection = new AMQPSocketConnection($connectionConfig['host'], $connectionConfig['port'], $connectionConfig['login'], $connectionConfig['password']);

        $this->channel = $this->connection->channel();

        $this->channel->exchange_declare($exchangeConfig['name'], $exchangeConfig['type'], false, true, true);

        $this->exchange = $exchangeConfig['name'];

        foreach ($queues as $queueName) {

            $this->channel->queue_declare($queueName, false, true, false, false);

            $this->channel->queue_bind($queueName, $exchangeConfig['name']);
        }
    }

    /**
     * @return \AMQPChannel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return \AMQPConnection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return \AMQPExchange
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * @return \AMQPQueue[]
     */
    public function getQueues()
    {
        return $this->queues;
    }

    public function send($text) {
        $msg = new AMQPMessage($text, array('content_type' => 'text/plain', 'delivery_mode' => 2));
        $this->channel->basic_publish($msg, $this->exchange);
    }
}