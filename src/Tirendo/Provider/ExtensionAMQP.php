<?php

namespace Tirendo\Provider;

use Tirendo\StopWatch;

/**
 * Class Provider
 * @package Tirendo\AMQP
 */
final class ExtensionAMQP
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
        $this->connection = new \AMQPConnection($connectionConfig);
        $this->connection->connect();
        $this->stats['connection_open'] = StopWatch::stopEvent('connection');

        $this->channel = new \AMQPChannel($this->connection);

        $this->exchange = new \AMQPExchange($this->channel);
        $exchange_name = $exchangeConfig['name'] . '_extension';
        $this->exchange->setName($exchange_name);
        $this->exchange->setType($exchangeConfig['type']);
        $this->exchange->setFlags($exchangeConfig['flags']);

        $this->exchange->declareExchange();

        foreach ($queues as $queueName) {
            $queue = new \AMQPQueue($this->channel);
            $queue->setName($queueName);
            $queue->setFlags(AMQP_DURABLE);
            $queue->declareQueue();
            $queue->bind($exchange_name, '');

            $this->queues[] = $queue;
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

    public function send($msg)
    {
        StopWatch::startEvent('send');
        $this->exchange->publish(
            $msg,
            null,
            0,
            array(
                'delivery_mode' => 2,
                'Content-type'  => 'text/plain'
            )
        );
        $this->stats['send'][] = StopWatch::stopEvent('send');
    }

    public function close()
    {
        StopWatch::startEvent('close');
        $this->connection->pdisconnect();
        $this->stats['connection_close'] = StopWatch::stopEvent('close');
    }

    public function getStats()
    {
        return $this->stats;
    }
}