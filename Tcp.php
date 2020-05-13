<?php
/**
 * Created by PhpStorm.
 * User: yangkai
 * Date: 2020/4/29
 * Time: 21:51
 */

namespace yangkai\server\timer;

use yangkai\server\timer\exceptions\SocketException;
use yangkai\server\timer\interfaces\TimerChannelInterface;

/**
 * Class Tcp
 * @package yangkai\server\timer
 */
class Tcp extends Base implements TimerChannelInterface
{

    //一次性通信
    private function Channel(string $string):string
    {
        $this->SocketTcp($this->Config::$IP??"127.0.0.1",$this->Config::$PORT??"8484");
        $this->Write($string);
        $reader = $this->Reader();
        $this->Close();
        return $reader;
    }

    /**
     * Tcp constructor.
     * 初始化需要提供一个初始任务服务器,可以使用 Config 配置对象来定义初始化配置
     * @param string $token
     * @param string $ip
     * @param Config $Config
     */
    public function __construct(string $token,string $ip,Config $Config)
    {
        $this->SetServer($token,$ip);

        $this->Config = $Config;
        $this->Timeout = $this->Config::$TIMEOUT;
        $this->ReaderLength = $this->Config::$READER_LENGTH;
        return $this;
    }

    //设置需要调度的任务服务器
    public function SetServer(string $token,string $ip):void
    {
        $this->token = $token;
        $this->ip = $ip;
    }

    /**
     * 添加到任务调度器
     * @param \DateTime $date
     * @param string $cmd
     * @return string
     * @throws SocketException
     */
    public function TaskAdd(\DateTime $date,string $cmd):string
    {
        if ( empty($this->token) || empty($this->ip) ) {
            throw new SocketException(SocketException::MESSAGE_TASK_SERVER_CONFIG,SocketException::CODE_TASK_SERVER_CONFIG);
        }
        $cmd = str_replace("\"","",$cmd);
        $date = $date->format("YmdHis");

        $string = "{$this->token}@{$this->ip} -t={$date} --CMD=\"{$cmd}\"\n";
        return $this->Channel($string);
    }

    /**
     * 查看未调度的任务数量
     * @return int
     * @throws SocketException
     */
    public function GetTaskNumber():int
    {
        if ( empty($this->token) || empty($this->ip) ) {
            throw new SocketException(SocketException::MESSAGE_TASK_SERVER_CONFIG,SocketException::CODE_TASK_SERVER_CONFIG);
        }

        $string = self::TCP_STRING_STATUS. chr(self::SPLIT_SEARCH) .$this::STATUS_TASK_NUMBER."\n";
        [$taskNumberKey,$taskNumberValue] = explode(":",trim($this->Channel($string)));

        return $taskNumberValue;
    }

    /**
     * task.date.number 的所有资源数量
     * @return array
     * @throws SocketException
     */
    public function GetTaskDateNumberAll() : array
    {
        if ( empty($this->token) || empty($this->ip) ) {
            throw new SocketException(SocketException::MESSAGE_TASK_SERVER_CONFIG,SocketException::CODE_TASK_SERVER_CONFIG);
        }
        //获取数据
        $string = self::TCP_STRING_STATUS . chr(self::SPLIT_SEARCH) .$this::STATUS_TASK_DATE_NUMBER."\n";
        [$taskNumberKey,$taskNumberValue] = explode(chr(self::SPLIT_SEARCH),trim($this->Channel($string)));

        //格式化task.date.number 数据
        return $this->StatusTaskDateNumberAnalysis($taskNumberValue);
    }

    /**
     * task.date.number 的数据获取某个时间段的
     * @param \DateTime $date
     * @return int
     */
    public function GetTaskDateNumberOne(\DateTime $date) : int
    {
        if ( empty($this->token) || empty($this->ip) ) {
            throw new SocketException(SocketException::MESSAGE_TASK_SERVER_CONFIG,SocketException::CODE_TASK_SERVER_CONFIG);
        }

        $taskNumberValueDates = $this->GetTaskDateNumberAll();
        return $taskNumberValueDates[$date->format("YmdHis")] ?? 0;
    }

    /**
     * 获取timer 的所有状态
     * @return array
     * @throws SocketException
     */
    public function GetStatus():array
    {
        if ( empty($this->token) || empty($this->ip) ) {
            throw new SocketException(SocketException::MESSAGE_TASK_SERVER_CONFIG,SocketException::CODE_TASK_SERVER_CONFIG);
        }

        $string = self::TCP_STRING_STATUS."\n";
        $logs = explode("\n",trim($this->Channel($string)));
        $global = [];
        foreach ($logs as $log){
            $log = explode(chr(self::SPLIT_SEARCH),$log);
            $global[$log[0]] = trim(trim($log[1],"\n"));

            //task.date.number 数据解析成数组进行返回
            if ($log[0] == self::STATUS_TASK_DATE_NUMBER){
                $global[$log[0]] = $this->StatusTaskDateNumberAnalysis($global[$log[0]]);
            }

        }
        return $global;
    }

    /**
     * 获取所有的任务记录id
     * @return array
     */
    public function GetRecordAll(): array
    {
        //获取数据
        $string = self::TCP_STRING_RECORD . chr(self::SPLIT_SEARCH) ."all\n";
        $recordValues = explode(chr(self::SPLIT_VALUE),trim($this->Channel($string)));

        //格式化record 任务ids数据
        $recordAllValues = [];
        foreach ($recordValues as $value){
            $recordAllValues += $this->RecordIdsAnalysis($value);
        }
        return $recordAllValues;
    }

    /**
     * 获取某个时间段的任务记录id
     * @return array
     */
    public function GetRecordDate(\DateTime $date): array
    {
        //获取数据
        $string = self::TCP_STRING_RECORD . chr(self::SPLIT_SEARCH) . $date->format("YmdHis") . "\n";
        $recordValues = trim($this->Channel($string));
        return $this->RecordIdsAnalysis($recordValues);
    }

    /**
     * 删除某个时间段的所有任务
     * @param \DateTime $date
     * @return int
     */
    public function DeleteDate(\DateTime $date): int
    {
        //获取数据
        $string = self::TCP_STRING_DELETE . chr(self::SPLIT_SEARCH) . $date->format("YmdHis") . "\n";
        $deleteNumber = trim($this->Channel($string));
        return $deleteNumber;
    }

    /**
     * 删除某个时间段中的固定任务
     * @param \DateTime $date
     * @return int
     */
    public function DeleteId(\DateTime $date, int $id): int
    {
        //获取数据
        $string = self::TCP_STRING_DELETE . chr(self::SPLIT_SEARCH) . $date->format("YmdHis") . chr(self::SPLIT_KEYS) . $id . "\n";
        $deleteNumber = trim($this->Channel($string));
        return $deleteNumber;
    }



}
