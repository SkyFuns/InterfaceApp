<?php

namespace app\frontend\controller;

use think\Session;

class Index extends Base
{
    public function index()
    {
        $this->assign([
            'username'  => Session::get('username'),
        ]);
    	return $this->fetch("index");
    }

    public function main()
    {
        return $this->fetch("main");
    }

    /*public function cases()
    {
    	return $this->fetch("cases",[
            "list"=>Core::loadModel("Posts")->listPosts($this->param,3)
        ]);
    }
    public function about()
    {
    	return $this->fetch("about");
    }
    public function changlang(){
       Cookie::set("think_var",$this->param['lang']);
    }*/
}
