<?php
/**
 * Actuator.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/23 22:09
 */

namespace Dybasedev\Actuator;

use Dybasedev\Actuator\Pipe\PipeManager;
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
    protected $makers;

    public function registerMaker($name, $resource)
    {
        $this->makers[$name] = $resource;
    }

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

    public function call($name)
    {
        if (isset($this->makers[$name])) {
            $maker = $this->makers[$name];

            if ($maker instanceof Process) {
                return $maker->call();
            }

            if ($maker instanceof Performer) {
                return $this->play($maker);
            }

            if ($maker instanceof Closure) {
                $response = call_user_func($maker);

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

    }
}