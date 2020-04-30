<?php
/**
 * Created by PhpStorm.
 * User: yangkai
 * Date: 2020/4/29
 * Time: 20:03
 */

namespace yangkai\server\timer;


class Base
{

    //golang timer 状态查看
    const STATUS_TASK_NUMBER = "task.number";   //查看待执行任务数量
    const STATUS_TASK_DATE_NUMBER = "task.date.number"; //查看所有时间等待执行的任务数量

    //代理执行服务器
    protected $token;
    protected $ip;

    /**
     * 配置合集
     * @var Config
     */
    protected $Config;

    //通信资源流
    protected $resource;

    /**
     * TCP 即时通讯
     * @param string $ip
     * @param int $port
     * @throws \Exception
     */
    protected function SocketTcp($ip = "127.0.0.1",$port = 8484) : void
    {
        $this->resource = fsockopen("tcp://{$ip}",$port,$errCode,$errMsg,$this->Config::$TIMEOUT ?? 1);
        if ($errCode != 0 || $this->resource === false) {
            throw new \Exception($errMsg,$errCode);
        }else{
            stream_set_blocking($this->resource,$this->Config::$mode ?? 1);
        }
    }

    /**
     * UDP 即时通讯
     * @param string $ip
     * @param int $port
     * @throws \Exception
     */
    protected function SocketUdp($ip = "127.0.0.1",$port = 8484) : void
    {
        $this->resource = fsockopen("udp://{$ip}",$port,$errCode,$errMsg,$this->Config::$TIMEOUT ?? 1);
        if ($errCode == 0 || $this->resource === false) {
            throw new \Exception($errMsg,$errCode);
        }else{
            stream_set_blocking($this->resource,$this->Config::$MODE ?? 1);
        }
    }

    /**
     * Http 即时通讯
     * @param string $domain
     * @param int $port
     * @throws \Exception
     */
    protected function SocketHttp($domain = "127.0.0.1",$port = 8484) : void
    {
        $this->resource = fsockopen($domain,$port,$errCode,$errMsg,$this->Config::$TIMEOUT ?? 1);
        if ($errCode == 0 || $this->resource === false) {
            throw new \Exception($errMsg,$errCode);
        }else{
            stream_set_blocking($this->resource,$this->Config::$MODE ?? 1);
        }
    }

    /**
     * 通信发送
     * @param string $string
     */
    protected function Write(string $string)
    {
        fwrite($this->resource,$string);
    }

    /**
     * 读取通信内容
     * @return string
     */
    protected function Reader():string
    {
        return fread($this->resource,1024*($this->Config::$READER_LENGTH ?? 1));
    }

    /**
     * 关闭通信资源
     */
    protected function Close():void
    {
        fclose($this->resource);
    }


}

//$fp = fsockopen("127.0.0.1", 80, $errno, $errstr);
//if (!$fp) {
//    echo "ERROR: $errno - $errstr<br />\n";
//} else {
//
//    $out = "GET / HTTP/1.1\r\n";
//    $out .= "Host: 127.0.0.1\r\n\r\n";
//
//    fwrite($fp, $out);
//    var_dump(fread($fp,1024*1024));
//    fclose($fp);
//}