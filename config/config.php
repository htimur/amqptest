<?php

return [
    'connection' => [
        'host'     => '127.0.0.1',
        'port'     => '5672',
        'login'    => 'guest',
        'password' => 'guest',
    ],
    'exchange'   => [
        'name'  => 'amqp_test',
        'type'  => AMQP_EX_TYPE_FANOUT,
        'flags' => AMQP_DURABLE | AMQP_AUTODELETE
    ],
    'queues'     => [
        'amqp_test',
    ],
    'iterations' => [
        'message_send'    => 10,
        'connection' => 100,
    ],
];