<?php
/**
 * Pipe.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/27 22:43
 */

namespace Dybasedev\Actuator\Pipe;

class Pipe
{
    protected $spec;

    protected $pipe;

    protected $available = true;

    public function __construct($spec, &$pipe)
    {
        $this->spec = $spec;

        $this->pipe =& $pipe;
    }

    public function read($length)
    {
        return fread($this->pipe, $length);
    }

    public function readTo($length, callable $callback)
    {

    }

    public function close()
    {
        if ($this->available) {
            fclose($this->pipe);
            $this->available = false;
        }
    }

    public function __destroy()
    {
        $this->close();
    }
}