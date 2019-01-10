<?php
return [
	'roles' => [
		['root', '超级用户'],
        ['normal', '登录用户'],
		['vistor', '游客']
	],
	'permissions' => [
        ['lgoods/*', '商品的全部权限'],
        ['lattr/*', "属性管理全部权限"],
        ['lclassification/*', "分类管理全部权限"],
        ['lorder/*', "订单管理全部权限"],
        ['lcollect/*', '属性集合全部管理权限'],
        ['lrefund/*', '售后管理全部权限'],
        ['lsale-rule/*', "销售规则管理全部权限"],
        ['lfile/*', "文件管理全部权限"],
        ['ltrans/*', "交易管理全部权限"],
        ['lbanner/*', 'banner图全部管理权限'],
        ['lauth/*', "认证管理全部权限"],
        ['luser/*', "用户管理全部权限"],
        ['laction/*', '动作日志全部权限'],

    ],
	'assign' => [
        ['root', "lgoods/*'"]
        ,['root', "lattr/*'"]
        ,['root', "lclassification/*'"]
        ,['root', "lorder/*'"]
        ,['root', "lrefund/*'"]
        ,['root', "lsale-rule/*"]
        ,['root', "lfile/*'"]
        ,['root', "lauth/*'"]
        ,['root', "luser/*'"]
        ,['root', 'lcollect/*']
        ,['root', 'ltrans/*']
        ,['root', 'lbanner/*']
        ,['root', 'laction/*']



        ,['normal', "lauth/login"]
        ,['normal', "lgoods/list"]
        ,['normal', "lgoods/view"]
        ,['normal', "lfile/create"]

        ,['vistor', "lauth/login"]
        ,['vistor', "lgoods/list"]
        ,['vistor', "lgoods/view"]
        ,['vistor', "luser/create"]


    ]
];
