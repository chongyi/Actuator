<?php
/**
 * Actuator.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/23 22:09
 */

namespace Dybasedev\Actuator;

class Actuator
{
    protected $processes = [];

    public function createProcess($command, array $descriptorSpec)
    {
        $process = proc_open($command, $descriptorSpec, $pipes);

        if (is_resource($process)) {
            $id                   = $this->processIdGenerate();
            $this->processes[$id] = [$process, $pipes];

            return $this->createProcessHandler($id);
        }

        throw new \RuntimeException('Cannot open the process.');
    }

    public function processIdGenerate()
    {
        list($micro_sec, $sec) = explode(' ', microtime());

        return (int)(substr($sec, -5) . $micro_sec);
    }

    public function createProcessHandler($id)
    {
        return new ProcessHandler($this, $id);
    }

    public function destroyProcess($id)
    {
        if (isset($this->processes[$id][1])) {

            foreach ($this->processes[$id][1] as $pipe) {
                fclose($pipe);
            }

            unset($this->processes[$id][1]);
        }

        proc_close($this->processes[$id]);
        unset($this->processes[$id]);
    }
}