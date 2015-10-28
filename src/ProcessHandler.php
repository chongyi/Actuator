<?php
/**
 * ProcessHandler.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/24 18:27
 */

namespace Dybasedev\Actuator;

use Dybasedev\Actuator\Pipe\PipeManager;

class ProcessHandler
{
    protected $process;

    protected $pipeManager;

    protected $events;

    protected $available = true;

    public function __construct(&$process, PipeManager $manager)
    {
        $this->process =& $process;

        $this->pipeManager = $manager;
    }

    public function getPipeManger()
    {
        return $this->pipeManager;
    }

    public function registerEvent($event, callable $callback)
    {
        $this->events[$event] = $callback;
    }

    public function fire($event, $parameters = [])
    {
        if (isset($this->events[$event])) {
            return call_user_func_array($this->events[$event], $parameters);
        }

        return null;
    }

    public function close()
    {
        if ($this->available) {
            $this->available = false;
            unset($this->pipeManager);
            proc_close($this->process);

            $this->fire('closed');
        }
    }

    public function __destroy()
    {
        $this->close();
    }
}