<?php
/**
 * Created by PhpStorm.
 * User: yangkai
 * Date: 2020/4/29
 * Time: 20:03
 */

namespace yangkai\server\timer;

/**
 * Class Base
 * @package yangkai\server\timer
 */
class Base
{

    //TCP 通信特殊关键词
    const TCP_STRING_STATUS = 'status'; //查看任务状态
    const TCP_STRING_RECORD = 'record'; //查看任务中的记录ID
    const TCP_STRING_DELETE = 'delete'; //对任务进行删除

    //golang timer 状态查看
    const STATUS_TASK_NUMBER = "task.number";   //查看待执行任务数量
    const STATUS_TASK_DATE_NUMBER = "task.date.number"; //查看所有时间等待执行的任务数量

    //返回的字符串数据分割
    const SPLIT_SEARCH = 0x3A; 	//参数和数据的区分
    const SPLIT_KEYS = 0x2E;		//参数的类型区分
    const SPLIT_VALUE = 0x2C;	//内容的数据区分
    const SPLIT_VALUES = 0x7C;	//内容中的多个数据区分
    const SPLIT_KEY_VALUES_EQ = 0x3D; //确认key value 关系的符号

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
     * 解析status task.date.number 字符串成数组
     * @param string $string
     * @return array
     */
    protected function StatusTaskDateNumberAnalysis(string $string) : array
    {
        $taskNumberValue = array_filter(explode(chr(self::SPLIT_VALUE),$string));
        $taskNumberValueDate = [];
        foreach ($taskNumberValue as $key=>$value){
            [$date,$length] = explode(chr(self::SPLIT_KEY_VALUES_EQ),$value);
            $taskNumberValueDate[$date] = $length;
        }
        return $taskNumberValueDate;
    }

    /**
     * 解析status task.date.number 字符串成数组
     * @param string $string
     * @return array
     */
    protected function RecordIdsAnalysis(string $string) : array
    {
        [$recordKey,$recordValue] = explode(chr(self::SPLIT_KEY_VALUES_EQ),$string);
        $recordValue = explode(chr(self::SPLIT_VALUES),$recordValue);
        return [$recordKey => $recordValue];
    }

    /**
     * 关闭通信资源
     */
    protected function Close():void
    {
        fclose($this->resource);
    }


}