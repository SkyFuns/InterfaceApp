<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 回忆 <510974211@qq.com.com>
// +----------------------------------------------------------------------

function ret($code,$title = "",$content = "")
{
	$err['status'] = $code;
	$err['title'] = $title;
	$err['content'] = $content;
    return $err;
}