<?php
/**
 * ProcessHandler.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/24 18:27
 */

namespace Dybasedev\Actuator;

use Dybasedev\Actuator\Pipe\PipeManager;

/**
 * Class ProcessHandler
 *
 * 进程资源管理器，主要用于对进程资源的操作，包括对进程的管道的读写、资源的释放等。
 *
 * @package Dybasedev\Actuator
 *
 * @author  chongyi <xpz3847878@163.com>
 */
class ProcessHandler
{
    /**
     * @var resource $process 进程资源
     */
    protected $process;

    /**
     * @var PipeManager $pipeManager 管道管理器
     */
    protected $pipeManager;

    /**
     * @var array $events 注册的事件
     */
    protected $events;

    /**
     * @var bool 用于标识当前资源是否可被释放
     */
    protected $available = true;

    /**
     * 构造方法
     *
     * @param resource    $process 进程资源
     * @param PipeManager $manager 管道管理器实例
     */
    public function __construct(&$process, PipeManager $manager)
    {
        $this->process =& $process;

        $this->pipeManager = $manager;
    }

    /**
     * 取的进程管道管理器
     *
     * @return PipeManager
     */
    public function getPipeManager()
    {
        return $this->pipeManager;
    }

    /**
     * 注册事件
     *
     * @param string   $event    事件名称
     * @param callable $callback 事件回调
     */
    public function registerEvent($event, callable $callback)
    {
        $this->events[$event] = $callback;
    }

    /**
     * 触发事件
     *
     * @param string $event      事件名称
     * @param array  $parameters 传递给事件回调的参数
     *
     * @return mixed|null
     */
    protected function fire($event, $parameters = [])
    {
        if (isset($this->events[$event])) {
            return call_user_func_array($this->events[$event], $parameters);
        }

        return null;
    }

    /**
     * 关闭、释放当前进程资源
     */
    public function close()
    {
        if ($this->available) {
            $this->available = false;
            unset($this->pipeManager);
            proc_close($this->process);

            $this->fire('closed');
        }
    }

    /**
     * 析构函数
     */
    public function __destroy()
    {
        $this->close();
    }
}