# Actuator 执行器

轻松调用 shell 命令，同时实现双向进程管道的读写操作，以实现更为丰富的进程调用功能。

## 安装

通过 Composer 安装

`composer require chongyi/actuator dev-master`

## 使用

基本示例：

```php
use Dybasedev\Actuator\Actuator;

// 创建执行器实例
$actuator = new Actuator;

// 创建一个进程
$process = $actuator->createProcess('php -i');

// 从管道中读取进程输出的数据
while (!$process->getPipeManager()[1]->eof()) {
    print $process->getPipeManager()[1]->read(64);
}
```

管道的双向读写：

```php
use Dybasedev\Actuator\Actuator;

$actuator = new Actuator;

$printer = $actuator->createProcess('php -i');
$grep    = $actuator->createProcess('grep extension');

while (!$printer->getPipeManager()[1]->eof()) {
    // 从管道中读取进程输出的数据，同时向另一个进程的管道写入数据
    $grep->getPipeManager()[0]->write($printer->getPipeManager()[1]->read(64));
}

// 关闭 grep 进程的写入管道
$grep->getPipeManager()[0]->close();

// 从 grep 进程管道读取搜索结果
while (!$grep->getPipeManager()[1]->eof()) {
    print $grep->getPipeManager()[1]->read(64);
}
```

上述例子等同于执行命令 `php -i|grep` 。对于更复杂的管道读写操作可以用更为灵活的方式进行。

## 计划更新

下一步打算利用协程概念实现更为强大的功能，敬请期待。