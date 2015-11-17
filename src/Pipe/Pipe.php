<?php
/**
 * Pipe.php
 *
 * Created by Chongyi
 * Date & Time 2015/10/27 22:43
 */

namespace Dybasedev\Actuator\Pipe;

/**
 * Class Pipe
 *
 * 管道对象。通过该对象的封装，可以方便的对管道进行读写操作。
 *
 * @package Dybasedev\Actuator\Pipe
 *
 * @author  chongyi <xpz3847878@163.com>
 */
class Pipe
{
    /**
     * @var int $spec 描述符
     */
    protected $spec;

    /**
     * @var resource $pipe 管道
     */
    protected $pipe;

    /**
     * @var bool 用于标识当前资源是否可被释放
     */
    private $available = true;

    /**
     * 构造函数
     *
     * @param int      $spec 描述符
     * @param resource $pipe 管道资源
     */
    public function __construct($spec, &$pipe)
    {
        $this->spec = $spec;

        $this->pipe =& $pipe;

        stream_set_blocking($this->pipe, 0);
    }

    /**
     * 从当前管道读取数据，当遇到 EOF 或读取了制定长度后终止读取。
     *
     * @param int $length 读取的长度
     *
     * @return string
     */
    public function read($length)
    {
        return fread($this->pipe, $length);
    }

    /**
     * 获取管道资源流中的内容
     *
     * @param int|null $length
     * @param int|null $offset
     *
     * @return string
     */
    public function getContent()
    {
        $args = func_get_args();

        array_unshift($args, $this->pipe);

        return call_user_func_array('stream_get_contents', $args);
    }

    /**
     * 检测是否读到了尾部
     *
     * @return bool
     */
    public function eof()
    {
        return feof($this->pipe);
    }

    /**
     * 向该管道写入数据
     *
     * @param mixed    $data   写入的数据
     * @param int|null $length 写入的长度，若指定了 $length 则当写入了超过 $length 字节内容后即会停止。
     *
     * @return int
     */
    public function write($data, $length = null)
    {
        if (is_null($length)) {
            return fwrite($this->pipe, $data);
        }

        return fwrite($this->pipe, $data, $length);
    }

    /**
     * 释放资源
     */
    public function close()
    {
        if ($this->available) {
            fclose($this->pipe);
            $this->available = false;
        }
    }

    public function readable()
    {
        if ($this->spec == 1 || $this->spec == 2) {
            return true;
        }

        return false;
    }

    public function writeable()
    {
        if ($this->spec == 0) {
            return true;
        }

        return false;
    }

    /**
     * 析构函数
     */
    public function __destroy()
    {
        $this->close();
    }
}