<?php
/**
 * Created by PhpStorm.
 * User: yangkai
 * Date: 2020/4/29
 * Time: 21:08
 */

namespace yangkai\server\timer\exceptions;


class SocketException extends \Exception
{
    const CODE_TASK_SERVER_CONFIG = 101; //执行任务服务器配置异常

    const MESSAGE_TASK_SERVER_CONFIG = "数据配置异常"; //执行任务服务器配置异常



}