<?php

namespace Tirendo\Provider;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Tirendo\StopWatch;

/**
 * Class Provider
 * @package Tirendo\AMQP
 */
final class PhpStreamAMQP
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

    private $stats = [];

    /**
     * @param array $connectionConfig
     * @param array $exchangeConfig
     * @param array $queues
     */
    function __construct(array $connectionConfig, array $exchangeConfig, array $queues)
    {
        StopWatch::startEvent('connection');
        $this->connection = new AMQPConnection($connectionConfig['host'], $connectionConfig['port'], $connectionConfig['login'], $connectionConfig['password']);
        $this->stats['connection_open'] = StopWatch::stopEvent('connection');

        $this->channel = $this->connection->channel();

        $exchangeName = $exchangeConfig['name'] . '_stream';
        $this->channel->exchange_declare($exchangeName, $exchangeConfig['type'], false, true, true);

        $this->exchange = $exchangeName;

        foreach ($queues as $queueName) {

            $this->channel->queue_declare($queueName, false, true, false, false);

            $this->channel->queue_bind($queueName, $exchangeName);
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
        StopWatch::startEvent('send');
        $msg = new AMQPMessage($text, array('content_type' => 'text/plain', 'delivery_mode' => 2));
        $this->channel->basic_publish($msg, $this->exchange);
        $this->stats['send'][] = StopWatch::stopEvent('send');
    }

    public function close() {
        StopWatch::startEvent('close');
        $this->connection->close();
        $this->stats['connection_close'] = StopWatch::stopEvent('close');
    }

    public function getStats()
    {
        return $this->stats;
    }
}