<?php

namespace app\frontend\validate;
class Purchase extends FrontendBase
{
	// 验证规则
    protected $rule = [
        'model'              => 'require',
    ];

    // 验证提示
    protected $message = [
        'model.require'          => '品牌型号必须',
    ];

    // 应用场景
    protected $scene = [
        'add'  =>  ['model']
    ];
}


