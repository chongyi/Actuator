<?php
/**
 * PipeManager.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/27 22:43
 */

namespace Dybasedev\Actuator\Pipe;

use ArrayAccess;

class PipeManager implements ArrayAccess
{
    protected $pipes;

    /**
     * @var array
     */
    protected $handler;

    public function __construct(&$pipes)
    {
        $this->pipes =& $pipes;

        foreach ($this->pipes as $spec => &$pipe) {
            $this->handler[$spec] = new Pipe($spec, $pipe);
        }
    }

    /**
     * @param $spec
     *
     * @return Pipe
     */
    public function get($spec)
    {
        return $this->handler[$spec];
    }

    public function offsetSet($spec, $pipe)
    {
        // Forbidden
        return false;
    }

    public function offsetGet($spec)
    {
        return $this->get($spec);
    }

    public function offsetUnset($spec)
    {
        $this->handler[$spec]->close();

        unset($this->handler[$spec]);
        unset($this->pipes[$spec]);
    }

    public function offsetExists($spec)
    {
        return isset($this->handler[$spec]);
    }
}