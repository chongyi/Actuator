<?php
/**
 * PipeManager.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/27 22:43
 */

namespace Dybasedev\Actuator\Pipe;

use ArrayAccess;

/**
 * Class PipeManager
 *
 * 管道管理器，用于管理多个管道。
 *
 * @package Dybasedev\Actuator\Pipe
 *
 * @author chongyi <xpz3847878@163.com>
 */
class PipeManager implements ArrayAccess
{
    /**
     * @var array $pipes 管道
     */
    protected $pipes;

    /**
     * @var array $handlers Pipe 对象数组
     */
    protected $handlers;

    /**
     * 构造函数
     *
     * @param $pipes
     */
    public function __construct(&$pipes)
    {
        $this->pipes =& $pipes;

        foreach ($this->pipes as $spec => &$pipe) {
            // 创建管道对象
            $this->handlers[$spec] = new Pipe($spec, $pipe);
        }
    }

    /**
     * 销毁
     */
    public function destroy()
    {
        foreach ($this->handlers as $pipe) {
            $pipe->close();
        }
    }

    /**
     * 取的管道对象
     *
     * @param $spec
     *
     * @return Pipe
     */
    public function get($spec)
    {
        return $this->handlers[$spec];
    }

    /**
     * 数组式访问接口实现方法
     *
     * @param mixed $spec
     * @param mixed $pipe
     *
     * @return bool
     */
    public function offsetSet($spec, $pipe)
    {
        // Forbidden
        return false;
    }

    /**
     * 数组式访问接口实现方法
     *
     * @param mixed $spec
     *
     * @return Pipe
     */
    public function offsetGet($spec)
    {
        return $this->get($spec);
    }

    /**
     * 数组式访问接口实现方法
     *
     * @param mixed $spec
     */
    public function offsetUnset($spec)
    {
        $this->handlers[$spec]->close();

        unset($this->handlers[$spec]);
        unset($this->pipes[$spec]);
    }

    /**
     * 数组式访问接口实现方法
     *
     * @param mixed $spec
     *
     * @return bool
     */
    public function offsetExists($spec)
    {
        return isset($this->handlers[$spec]);
    }

    /**
     * @return Pipe|null
     */
    public function stdIn()
    {
        return isset($this[0]) ? $this[0] : null;
    }

    /**
     * @return Pipe|null
     */
    public function stdOut()
    {
        return isset($this[1]) ? $this[1] : null;
    }

    /**
     * @return Pipe|null
     */
    public function stdError()
    {
        return isset($this[2]) ? $this[2] : null;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        $this->destroy();
    }
}