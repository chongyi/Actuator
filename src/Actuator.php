<?php
/**
 * Actuator.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/23 22:09
 */

namespace Dybasedev\Actuator;

use Dybasedev\Actuator\Pipe\PipeManager;
use Dybasedev\Actuator\Pipe\PipeOperator;
use InvalidArgumentException;
use RuntimeException;
use Closure;

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
    protected $actuators;

    /**
     * 添加一个可执行组件
     *
     * @param string $name
     * @param Process|Performer $actuator
     */
    public function addPerformer($name, $actuator)
    {
        $this->actuators[$name] = $actuator;
    }

    /**
     * 指定一个执行者运作
     *
     * @param Performer $performer
     *
     * @return mixed
     */
    public function play(Performer $performer)
    {
        $result = $performer->process()->call(function (PipeManager $pipe) use ($performer) {
            if ($pipe->stdIn()) {
                $pipe->stdIn()->write($performer->write());
                $pipe->stdIn()->close();
            }

            $response = '';

            if ($pipe->stdOut()) {
                while (!$pipe->stdOut()->eof()) {
                    $response .= $pipe->stdOut()->read(1024);
                }

                $pipe->stdOut()->close();
            }

            $pipe->destroy();

            return $response;
        });

        return $performer->format($result);
    }

    /**
     * 调用一个执行器组件
     *
     * @param $name
     *
     * @return mixed
     */
    public function call($name)
    {
        if (isset($this->actuators[$name])) {
            $actuator = $this->actuators[$name];

            if ($actuator instanceof Process) {
                return $actuator->call();
            }

            if ($actuator instanceof Performer) {
                return $this->play($actuator);
            }

            if ($actuator instanceof Closure) {
                $response = call_user_func($actuator);

                if (!$response instanceof Process) {
                    return $response->call();
                }

                return $response;
            }

            throw new RuntimeException;
        }

        throw new InvalidArgumentException;
    }

    public function let($process)
    {
        if ($process instanceof Process) {
            if (!$process->executing()) {
                $process = clone $process;
            }

            $process->execute();

            return $this->newPipeOperator($process);
        }

        if ($process instanceof Performer) {
            $new = $process->process();

            $new->execute();

            if ($new->pipes()->stdIn()) {
                $new->pipes()->stdIn()->write($process->write());
            }

            return $this->newPipeOperator($new);
        }

        if (is_string($process)) {
            if (isset($this->actuators[$process])) {
                return $this->let($this->actuators[$process]);
            }
        }

        throw new InvalidArgumentException;
    }

    public function newPipeOperator(Process $process)
    {
        $operator = new PipeOperator($this);

        return $operator->start($process);
    }
}