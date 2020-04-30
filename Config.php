<?php
/**
 * Created by PhpStorm.
 * User: yangkai
 * Date: 2020/4/29
 * Time: 22:17
 */

namespace yangkai\server\timer;


class Config
{
    //调度服务器
    public static $SERVER_IP = "127.0.0.1";
    public static $SERVER_PORT = "8484";
    public static $TIMEOUT = 3;     //通信超时时间
    public static $READER_LENGTH = 1; //最多允许多少KB
    public static $MODE = 1;    //设置堵塞模式 1：堵塞模式，0：非堵塞模式 ，堵塞模式

}