<?php

namespace app\frontend\controller;
use \think\Request;
use \tpfcore\Core;
use think\Session;

class Trackrecord extends FrontendBase{

    public function followe_records()
    {
        return $this->fetch("followe_records");
    }

    public function addFur()
    {

        if(!isset($_POST['parms']['fur_title']) || empty($_POST['parms']['fur_title']))
        {
            return ret(1,'请求失败','跟进记录不能为空');
        }
        if(!isset($_POST['parms']['fur_time']) || empty($_POST['parms']['fur_time']))
        {
            return ret(1,'请求失败','跟进时间不能为空');
        }

        $username = Session::get('username');
        if(empty($username))
        {
            return ret(1,'请求失败','已超时,请重新登录');
        }

        $user['username'] = $username;

        $users = db('user')->where($user)->field('id')->find();

        if(empty($users))
        {
            return ret(1,'请求失败','已超时,请重新登录');
        }
        $Calculate['uid'] = $users['id'];

        $Calculaterecord = db('Calculaterecord')->where($Calculate)->field('id')->find();

        if(!empty($Calculaterecord))
        {
            $cal_parms['cid'] = $Calculaterecord['id'];
            $cal_parms['title'] = $_POST['parms']['fur_title'];
            $cal_parms['time'] = $_POST['parms']['fur_time'];
            $cal_parms['content'] = $_POST['parms']['fur_content'];
            $trackrecord = model('Trackrecord');
            $trackrecord->data($cal_parms);
            $trackrecord->save();
            if(empty($trackrecord->id))
            {
                return ret(1,'请求失败','请核实数据是否规范');
            }
            return ret(0,'请求成功');
        }




    }

}