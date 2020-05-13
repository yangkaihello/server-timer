<?php
/**
 * Created by PhpStorm.
 * User: yangkai
 * Date: 2020/4/30
 * Time: 18:16
 */

namespace yangkai\server\timer\interfaces;


use yangkai\server\timer\Config;
use yangkai\server\timer\exceptions\SocketException;

interface TimerChannelInterface
{

    /**
     * TimerChannelInterface constructor.
     * 初始化需要提供一个初始任务服务器$token,$ip,可以使用 \yangkai\server\timer\Config 配置对象来定义初始化配置
     * @param string $token
     * @param string $ip
     * @param Config $Config
     */
    public function __construct(string $token,string $ip,Config $Config);

    //设置需要调度的任务服务器配置
    public function SetServer(string $token,string $ip) : void;


    /**
     * 添加到任务调度器
     * @param \DateTime $date
     * @param string $cmd
     * @return string
     * @throws SocketException
     */
    public function TaskAdd(\DateTime $date,string $cmd) : string;

    /**
     * 查看未调度的任务数量
     * @return int
     * @throws SocketException
     */
    public function GetTaskNumber() : int;


    /**
     * task.date.number 的所有资源数量
     * @return array
     * @throws SocketException
     */
    public function GetTaskDateNumberAll() : array;

    /**
     * task.date.number 的数据获取某个时间段的
     * @param \DateTime $date
     * @return int
     */
    public function GetTaskDateNumberOne(\DateTime $date) : int;

    /**
     * 获取timer 的所有状态
     * @return array
     * @throws SocketException
     */
    public function GetStatus() : array;

    /**
     * 获取所有的任务记录id
     * @return array
     */
    public function GetRecordAll() : array;

    /**
     * 获取某个时间段的任务记录id
     * @return array
     */
    public function GetRecordDate(\DateTime $date) : array;

    /**
     * 删除某个时间段的所有任务
     * @param \DateTime $date
     * @return int
     */
    public function DeleteDate(\DateTime $date) : int;

    /**
     * 删除某个时间段中的固定任务
     * @param \DateTime $date
     * @return int
     */
    public function DeleteId(\DateTime $date,int $id) : int;

}