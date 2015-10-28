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

        stream_set_blocking($this->pipe, 0);
    }

    public function read($length)
    {
        return fread($this->pipe, $length);
    }

    public function eof()
    {
        return feof($this->pipe);
    }

    public function write($data, $length = null)
    {
        if (is_null($length)) {
            return fwrite($this->pipe, $data);
        }

        return fwrite($this->pipe, $data, $length);
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