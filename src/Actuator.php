<?php
/**
 * Actuator.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/18 22:43
 */

namespace Dybasedev\Actuator;

use Exception;

/**
 * Class Actuator
 *
 * 执行器，对 PHP 程序执行函数的封装，用于快速实现强大的程序控制、执行能力，对于与系统 shell 命令交互时提供便利。
 *
 * @package Dybasedev\Actuator
 */
class Actuator
{
    /**
     * @var string 执行的命令
     */
    protected $command;

    /**
     * @var string 工作目录
     */
    protected $commandWorkDirectory;

    /**
     * @var array 描述符
     */
    protected $descriptors;

    /**
     * @var PipeOperatorInterface 管道操作控制器
     */
    protected $process;

    /**
     * 设置命令
     *
     * @param string $command 具体的命令
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * 返回当前设置的命令
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * 设置描述符
     *
     * @param int|array $descriptor
     * @param mixed     $spec
     */
    public function setDescriptorSpec($descriptor, $spec = null)
    {
        if (is_array($descriptor)) {
            $this->descriptors = $descriptor;
        } else {
            $this->descriptors[$descriptor] = $spec;
        }
    }

    /**
     * 返回描述符
     *
     * @return array
     */
    public function getDescriptorSpec()
    {
        return $this->descriptors;
    }

    /**
     * 设置工作目录
     *
     * @param string $directory
     */
    public function setCommandWorkDirectory($directory)
    {
        $this->commandWorkDirectory = $directory;
    }

    /**
     * @return string
     */
    public function getCommandWorkDirectory()
    {
        if (is_null($this->commandWorkDirectory)) {
            $this->setCommandWorkDirectory(realpath($_SERVER['SCRIPT_FILENAME']));
        }

        return $this->commandWorkDirectory;
    }

    /**
     * @param PipeOperatorInterface|null $pipeController
     *
     * @return ProcessController|PipeOperatorInterface
     */
    public function createProcessController(PipeOperatorInterface $pipeController = null)
    {
        if (is_null($pipeController)) {
            $pipeController = new ProcessController($this);
        }

        return $pipeController;
    }

    public function process(callable $callback)
    {
        $resource = proc_open($this->getCommand(), $this->getDescriptorSpec(), $pipes, $this->getCommandWorkDirectory());

        if (is_resource($resource)) {
            $this->process = $this->createProcessController();
            $this->process->setProcessResource($resource);

            try {
                call_user_func_array($callback, [$this->process]);
            } catch (Exception $e) {
                $this->process->destroy();
            }
        }
    }
}