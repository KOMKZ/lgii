<?php
return [
	'roles' => [
		['root', '超级用户'],
        ['normal', '登录用户'],
		['vistor', '游客']
	],
	'permissions' => [
        ['goods/*', '商品的全部权限'],
        ['lattr/*', "属性管理全部权限"],
        ['lclassification/*', "分类管理全部权限"],
        ['lorder/*', "订单管理全部权限"],
        ['lrefund/*', '售后管理全部权限'],
        ['lsale-rule/*', "销售规则管理全部权限"],
        ['lfile/*', "文件管理全部权限"],
        ['auth/*', "认证管理全部权限"],
        ['user/*', "用户管理全部权限"],
	],
	'assign' => [
        ['root', "goods/*'"]
        ,['root', "lattr/*'"]
        ,['root', "lclassification/*'"]
        ,['root', "lorder/*'"]
        ,['root', "lrefund/*'"]
        ,['root', "lsale-rule/*"]
        ,['root', "lfile/*'"]
        ,['root', "auth/*'"]
        ,['root', "user/*'"]

        ,['normal', "auth/login"]
        ,['normal', "goods/list"]
        ,['normal', "goods/view"]
        ,['normal', "lfile/create"]
        
        ,['vistor', "auth/login"]
        ,['vistor', "goods/list"]
        ,['vistor', "goods/view"]


	]
];
