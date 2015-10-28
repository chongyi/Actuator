<?php
/**
 * Actuator.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/23 22:09
 */

namespace Dybasedev\Actuator;

use Dybasedev\Actuator\Pipe\PipeManager;
use SplObjectStorage;

/**
 * Class Actuator
 *
 * 执行器，对 PHP 程序执行函数的封装，用于快速实现强大的程序控制、执行能力，对于与系统 shell 命令交互时提供便利。
 *
 * @package Dybasedev\Actuator
 *
 * @author  chongyi <xpz3847878@163.com>
 */
class Actuator
{
    /**
     * @var SplObjectStorage $processes 进程对象
     */
    protected $processes;

    /**
     * @var string|null $directory 工作目录
     */
    protected $directory;

    public function __construct()
    {
        $this->processes = new SplObjectStorage();
    }

    /**
     * 执行一个 shell 命令（启动一个进程）
     *
     * @param string      $command        命令和参数
     * @param array|null  $descriptorSpec 描述符
     * @param string|null $workDirectory  工作目录
     *
     * @return ProcessHandler
     */
    public function createProcess($command, array $descriptorSpec = null, $workDirectory = null)
    {
        if (is_null($descriptorSpec)) {
            $descriptorSpec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        }

        if (is_null($workDirectory)) {
            $workDirectory = $this->directory;
        }

        $process = proc_open($command, $descriptorSpec, $pipes, $workDirectory);

        if (is_resource($process)) {
            $pipeManager    = $this->createProcessPipeManger($pipes);
            $processHandler = $this->createProcessHandler($process, $pipeManager);

            $processHandler->registerEvent('closed', function () use ($processHandler) {
                $this->processes->detach($processHandler);
            });

            $this->processes->attach($processHandler);

            return $processHandler;
        }

        throw new \RuntimeException('Cannot open the process.');
    }

    /**
     * 获取设置的工作目录
     *
     * @return null|string 获取设置的工作目录
     */
    public function getWorkDirectory()
    {
        return $this->directory;
    }

    /**
     * 设置工作目录（绝对路径）
     *
     * @param string $directory 工作目录，该值应当是一个绝对路径
     */
    public function setWorkDirectory($directory)
    {
        $this->directory = $directory;
    }

    /**
     * 创建进程管道管理器
     *
     * @param array $pipes
     *
     * @return PipeManager
     */
    protected function createProcessPipeManger(array &$pipes)
    {
        return new PipeManager($pipes);
    }

    /**
     * 创建进程资源管理器
     *
     * @param resource $process
     * @param PipeManager $manager
     *
     * @return ProcessHandler
     */
    protected function createProcessHandler(&$process, PipeManager $manager)
    {
        return new ProcessHandler($process, $manager);
    }

    /**
     * 销毁执行的进程
     *
     * @param ProcessHandler $process
     */
    public function destroyProcess(ProcessHandler $process)
    {
        $process->close();

        $this->processes->detach($process);
    }
}