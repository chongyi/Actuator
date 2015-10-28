# Actuator 执行器

轻松调用 shell 命令，同时实现双向进程管道的读写操作，以实现更为丰富的进程调用功能。

## 安装

通过 Composer 安装

`composer require chongyi/actuator dev-master`

## 使用

```php
use Dybasedev\Actuator;

// 创建执行器实例
$actuator = new Actuator;

// 创建一个进程
$process = $actuator->createProcess('php -i');

// 从管道中读取进程输出的数据
while (!$process->getPipeManger()[1]->eof()) {
    print $process->getPipeManager()[1]->read(64);
}

```