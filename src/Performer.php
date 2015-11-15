<?php
/**
 * Performer.php
 *
 * Created by Chongyi
 * Date & Time 2015/11/15 21:40
 */

namespace Dybasedev\Actuator;

abstract class Performer
{
    /**
     * @return Process
     */
    abstract public function process();

    /**
     * @return string|null
     */
    public function write()
    {
        return null;
    }

    /**
     * @param string $origin
     *
     * @return mixed
     */
    public function format($origin)
    {
        return $origin;
    }
}