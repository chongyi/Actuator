<?php
/**
 * ProcessHandler.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/24 18:27
 */

namespace Dybasedev\Actuator;

class ProcessHandler
{
    protected $processId;

    protected $actuator;

    public function __construct(Actuator $actuator, $processId)
    {
        $this->actuator = $actuator;

        $this->processId = $processId;
    }

    public function __destroy()
    {

    }
}