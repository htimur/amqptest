<?php

namespace Tirendo;


class StopWatch
{
    private static $checkpoints = [];

    public static function startEvent($event)
    {
        self::$checkpoints[$event] = microtime(true);
    }

    public static function stopEvent($event)
    {
        $start = self::$checkpoints[$event];
        unset(self::$checkpoints[$event]);
        return round((microtime(true) - $start), 3);
    }
} 