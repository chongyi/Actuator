<?php
/**
 * PipeController.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/18 22:48
 */

namespace Dybasedev\Actuator;

use ArrayAccess;

class ProcessController implements PipeOperatorInterface, ArrayAccess
{
    /**
     * @var array 创建的管道
     */
    protected $pipes;

    /**
     * @var Actuator 执行器实例
     */
    protected $actuator;

    /**
     * @var resource 进程资源
     */
    protected $resource;

    public function __construct(Actuator $actuator, array &$pipes = null)
    {
        if (!is_null($pipes)) {
            $this->setPipes($pipes);
        }

        $this->actuator = $actuator;
    }

    /**
     * 添加管道
     *
     * @param $pipes
     */
    public function setPipes(&$pipes)
    {
        $this->pipes = &$pipes;
    }

    /**
     * 设置进程资源
     *
     * @param $resource
     */
    public function setProcessResource($resource)
    {
        $this->resource = $resource;
    }

    public function write($data, $handle = 0)
    {
        fwrite($this->pipes[$handle], $data);
    }

    public function read($handle = 1)
    {
        return stream_get_contents($this->pipes[$handle]);
    }

    public function offsetExists($offset)
    {
        return isset($this->pipes[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->pipes[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->pipes[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->pipes[$offset]);
    }

    public function destroy()
    {

    }

    public function __destruct()
    {
        $this->destroy();
    }
}