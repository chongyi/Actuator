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

class Actuator
{
    /**
     * @var SplObjectStorage
     */
    protected $processes;

    protected $directory;

    public function __construct()
    {
        $this->processes = new SplObjectStorage();
    }

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
            $pid            = $this->processIdGenerate();

            $processHandler->registerEvent('closed', function () use ($processHandler) {
                $this->processes->detach($processHandler);
            });

            $this->processes->attach($processHandler, ['pid' => $pid]);

            return $processHandler;
        }

        throw new \RuntimeException('Cannot open the process.');
    }

    public function getWorkDirectory()
    {
        return $this->directory;
    }

    public function setWorkDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function createProcessPipeManger(&$pipes)
    {
        return new PipeManager($pipes);
    }

    public function processIdGenerate()
    {
        list($micro_sec, $sec) = explode(' ', microtime());

        return (int)(substr($sec, -5) . $micro_sec . mt_rand(100, 999));
    }

    public function createProcessHandler($process, PipeManager $manager)
    {
        return new ProcessHandler($process, $manager);
    }

    public function destroyProcess(ProcessHandler $process)
    {
        $process->close();

        $this->processes->detach($process);
    }
}