<?php
/**
 * Process.php
 *
 * Created by Chongyi
 * Date & Time 2015/11/15 19:17
 */

namespace Dybasedev\Actuator;

use Dybasedev\Actuator\Contracts\DescriptorSpecInterface;
use Dybasedev\Actuator\Pipe\PipeManager;
use RuntimeException;
use ArrayAccess;

class Process implements ArrayAccess
{
    /**
     * @var string 执行的目标（包含命令和参数）
     */
    protected $target;

    /**
     * 描述符
     *
     * @var DescriptorSpecInterface $descriptorSpec
     */
    protected $descriptorSpec;

    /**
     * @var string $commandWorkDirectory 工作目录
     */
    protected $commandWorkDirectory;

    /**
     * @var array $environment 执行的环境变量
     */
    protected $environment;

    /**
     * @var resource $process 过程实例
     */
    protected $process;

    /**
     * @var PipeManager $pipeManager
     */
    protected $pipeManager;

    /**
     * @var bool
     */
    private $executing = false;

    /**
     * @param string                  $target
     * @param DescriptorSpecInterface $descriptorSpec
     * @param string|null             $cwd
     * @param array|null              $env
     */
    public function __construct($target, DescriptorSpecInterface $descriptorSpec, $cwd = null, array $env = null)
    {
        $this->target               = $target;
        $this->descriptorSpec       = $descriptorSpec;
        $this->commandWorkDirectory = $cwd;
        $this->environment          = $env;
    }

    /**
     * 执行当前对象
     *
     * @return Process|bool
     */
    public function execute()
    {
        $process = proc_open($this->target, $this->descriptorSpec->toArray(), $pipes, $this->commandWorkDirectory,
            $this->environment);

        if (is_resource($process)) {
            $this->process     = $process;
            $this->pipeManager = new PipeManager($pipes);
            $this->executing   = true;

            return $this;
        }

        return false;
    }

    /**
     * 发起命令调用
     *
     * 基于当前对象的设置信息，来发起一次调用，调用中产生的管道会以参数传入相关回调
     *
     * @param callable $callback
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    public function call(callable $callback = null)
    {
        $process = proc_open($this->target, $this->descriptorSpec->toArray(), $pipes, $this->commandWorkDirectory,
            $this->environment);

        if (is_resource($process)) {
            $pipe     = new PipeManager($pipes);

            $response = '';

            if (is_null($callback)) {
                if ($pipe->stdIn()) {
                    $pipe->stdIn()->close();
                }

                if ($pipe->stdOut()) {
                    while (!$pipe->stdOut()->eof()) {
                        $response .= $pipe->stdOut()->read(1024);
                    }
                }
            } else {
                $parameters = func_get_args();

                array_shift($parameters);
                array_unshift($parameters, $pipe);

                $response = call_user_func_array($callback, $parameters);
            }

            $pipe->destroy();

            if (proc_close($process) == -1) {
                throw new RuntimeException;
            }

            return $response;
        }

        throw new RuntimeException;
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
     * @return Pipe\Pipe
     */
    public function offsetGet($spec)
    {
        return $this->pipeManager->get($spec);
    }

    /**
     * 数组式访问接口实现方法
     *
     * @param mixed $spec
     */
    public function offsetUnset($spec)
    {
        $this->pipeManager->get($spec)->close();
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
        return isset($this->pipeManager[$spec]);
    }

    /**
     * @return PipeManager
     */
    public function pipes()
    {
        return $this->pipeManager;
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        if ($this->executing) {
            $this->pipeManager->destroy();
            proc_close($this->process);
        }
    }
}