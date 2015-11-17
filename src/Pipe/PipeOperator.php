<?php
/**
 * Created by Chongyi.
 * Date: 2015/11/17 0017
 * Time: 下午 5:35
 */

namespace Dybasedev\Actuator\Pipe;


use Dybasedev\Actuator\Actuator;
use Dybasedev\Actuator\Process;

class PipeOperator
{
    protected $actuator;

    protected $cluster;

    public function __construct(Actuator $actuator)
    {
        $this->actuator = $actuator;
    }

    public static function newOperator(Actuator $actuator)
    {
        return new static($actuator);
    }

    public function start(Process $process)
    {
        $this->cluster = [$process];

        return $this;
    }

    public function into($process)
    {

    }

    public function stop()
    {

    }
}