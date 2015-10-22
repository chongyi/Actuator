<?php
/**
 * PipeOperatorInterface.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/18 22:49
 */

namespace Dybasedev\Actuator;

interface PipeOperatorInterface
{
    /**
     * 销毁、关闭当前管道
     *
     * @return void
     */
    public function destroy();
}