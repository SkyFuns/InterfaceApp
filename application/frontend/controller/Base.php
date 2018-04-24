<?php

namespace app\frontend\controller;

use think\Session;

class Base extends FrontendBase
{
    public function _initialize()
    {
        if (empty(Session::get('username'))) {
            $this->redirect(url('/Login/login'));
        }
    }


}
