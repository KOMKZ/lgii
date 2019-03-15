<?php

return [

    'file_task_type' => [
        \lfile\models\FileEnum::TASK_CHUNK_UPLOAD => \Yii::t('app', "文件分片上传任务"),
    ],
    'file_task_status' => [
        \lfile\models\FileEnum::TASK_STATUS_INIT => \Yii::t('app', '初始化')
    ],

    'file_save_type' => [
        \lfile\models\drivers\Disk::NAME => \Yii::t('app', '本地存储'),
        \lfile\models\drivers\Oss::NAME => \Yii::t('app', 'Oss存储'),
    ],
    'file_is_private' => [
        1 => \Yii::t('app', '私有访问'),
        0 => \Yii::t('app', '公有访问')
    ],
    'file_is_tmp' => [
        1 => \Yii::t('app', '临时文件'),
        0 => \Yii::t('app', '永久文件')
    ],
    'file_category' => [
        'test' => '测试文件',
        'default' => '默认分类'
    ],
    'sr_object_type' => [
        \lgoods\models\sale\SaleRule::SR_TYPE_SKU => '特定商品',
        \lgoods\models\sale\SaleRule::SR_TYPE_GOODS => '商品',
        \lgoods\models\sale\SaleRule::SR_TYPE_CATEGORY => '分类',
        \lgoods\models\sale\SaleRule::SR_TYPE_ORDER => '订单'

    ],
    'u_status' => [
        \luser\models\user\UserEnum::STATUS_ACTIVE => Yii::t('app', '可用'),
        \luser\models\user\UserEnum::STATUS_NO_AUTH => Yii::t('app', "没有验证"),
        \luser\models\user\UserEnum::STATUS_LOCKED => Yii::t('app', '锁定状态')
    ],
    'u_auth_status' => [
        \luser\models\user\UserEnum::NOT_AUTH => Yii::t('app', '未验证'),
        \luser\models\user\UserEnum::HAD_AUTH => Yii::t('app', "已验证")
    ],
    /*



	'g_status' => [
		\common\models\goods\ar\Goods::STATUS_DRAFT => \Yii::t('app', "草稿"),
		\common\models\goods\ar\Goods::STATUS_ON_SALE => \Yii::t('app', "上架"),
		\common\models\goods\ar\Goods::STATUS_ON_NOT_SALE => \Yii::t('app', "下架"),
		\common\models\goods\ar\Goods::STATUS_FORBIDDEN => \Yii::t('app', "禁止销售"),
		\common\models\goods\ar\Goods::STATUS_DELETE => \Yii::t('app', "删除"),
	],
	'g_atr_type' => [
		\common\models\goods\ar\GoodsAttr::ATR_TYPE_META => \Yii::t('app', '元信息型属性'),
		\common\models\goods\ar\GoodsAttr::ATR_TYPE_SKU => \Yii::t('app', 'sku型属性'),
		\common\models\goods\ar\GoodsAttr::ATR_TYPE_OPTION => \Yii::t('app', '选项型属性')
	],
	'g_atr_cls_type' => [
		\common\models\goods\ar\GoodsAttr::ATR_CLS_TYPE_CLS => Yii::t('app', "属性直属于分类"),
		\common\models\goods\ar\GoodsAttr::ATR_CLS_TYPE_GOODS => Yii::t('app', "属性直属于商品"),
	],
	'g_sku_status' => [
		\common\models\goods\ar\GoodsSku::STATUS_ON_SALE => Yii::t('app', "上架"),
		\common\models\goods\ar\GoodsSku::STATUS_ON_NOT_SALE => Yii::t('app', "下架"),
		\common\models\goods\ar\GoodsSku::STATUS_INVALID => Yii::t('app', "失效"),
		\common\models\goods\ar\GoodsSku::STATUS_DRAFT => Yii::t('app', '草稿')
	],
	'gs_type' => [
		\common\models\goods\ar\GoodsSource::TYPE_IMG => Yii::t('app', '图像')
	],
	'gs_cls_type' => [
		\common\models\goods\ar\GoodsSource::CLS_TYPE_SKU => Yii::t('app', '商品sku资源'),
		\common\models\goods\ar\GoodsSource::CLS_TYPE_GOODS => Yii::t('app', '商品资源'),
		\common\models\goods\ar\GoodsSource::CLS_TYPE_OPTION => Yii::t('app', '商品属性值资源')
	],
	'gs_use_type' => [
		\common\models\goods\ar\GoodsSource::U_SKU_M_IMG => Yii::t('app', 'sku记录主图'),
		\common\models\goods\ar\GoodsSource::U_GOODS_M_IMG => Yii::t('app', '商品展示轮播图'),
	],
	'mail_content_type' => [
		\common\models\mail\ar\Mail::CONTENT_TYPE_HTML => Yii::t('app', 'text/html'),
	],
	'mail_list_type' => [
		\common\models\mail\ar\Mail::LIST_TYPE_INLINE => Yii::t('app', '内联地址')
	],
	'message_type' => [
		\common\models\message\Message::TYPE_ONE => Yii::t('app', '私信')
		,\common\models\message\Message::TYPE_BOARD => Yii::t('app', '广播消息')
	]
	,'message_content_type' => [
		\common\models\message\Message::CONTENT_TYPE_PLAIN => Yii::t('app', '纯文本'),
		\common\models\message\Message::CONTENT_TYPE_TEMPLATE => Yii::t('app', '模板')
	]
	,'um_status' => [
		\common\models\user\ar\UserMessage::STATUS_UNREAD => Yii::t('app', '未读')
		,\common\models\user\ar\UserMessage::STATUS_HAD_READ => Yii::t('app', '已读')
	]
	,'t_pay_status' => [
		\common\models\trans\ar\Transaction::PAY_STATUS_NOPAY => Yii::t('app', '未支付')
		,\common\models\trans\ar\Transaction::PAY_STATUS_PAYED  => Yii::t('app', '已支付')
	]
	,'t_status' => [
		\common\models\trans\ar\Transaction::STATUS_INIT => Yii::t('app', '提交状态')
		,\common\models\trans\ar\Transaction::STATUS_CANCEL => Yii::t('app', '取消状态')
		,\common\models\trans\ar\Transaction::STATUS_PAYED => Yii::t('app', '已经支付状态')
		,\common\models\trans\ar\Transaction::STATUS_ERROR => Yii::t('app', "错误状态")
	]
	,'pt_pay_status' => [
		\common\models\pay\ar\PayTrace::PAY_STATUS_NOPAY => Yii::t('app', '未支付')
		,\common\models\pay\ar\PayTrace::PAY_STATUS_PAYED  => Yii::t('app', '已支付')
	]
	,'pt_status' => [
		\common\models\pay\ar\PayTrace::STATUS_INIT => Yii::t('app', '提交状态')
		,\common\models\pay\ar\PayTrace::STATUS_CANCEL => Yii::t('app', '取消状态')
		,\common\models\pay\ar\PayTrace::STATUS_PAYED => Yii::t('app', '已经支付状态')
		,\common\models\pay\ar\PayTrace::STATUS_ERROR => Yii::t('app', "错误状态")
	]
	,'pt_pre_order_type' => [
		\common\models\pay\ar\PayTrace::TYPE_DATA => Yii::t('app', "app调用数据")
		,\common\models\pay\ar\PayTrace::TYPE_URL => Yii::t('app', "pc端类url")
	]
	,'t_type' => [
		\common\models\trans\ar\Transaction::TYPE_CONSUME => Yii::t('app', "消费型交易")
		,\common\models\trans\ar\Transaction::TYPE_TRANSFER => Yii::t('app', "转账型交易")
		,\common\models\trans\ar\Transaction::TYPE_REFUND => Yii::t('app', "退款型交易")
	]
	,'currency_type' => [
		// todo function
		\common\models\pay\Currency::CNY => Yii::t('app', "人民币")
	]
	,'t_module' => [
		\common\models\trans\ar\Transaction::MODULE_ORDER => Yii::t('app', "订单模块")
	]
	,'payment' => [
		\common\models\pay\payment\Alipay::NAME => Yii::t('app', "支付宝")
		,\common\models\pay\payment\Wxpay::NAME => Yii::t('app', "微信")
	]
	,'ct_object_status' => [
		\common\models\order\ar\CartItem::ITEM_STATUS_VALID => Yii::t('app', '有效')
		,\common\models\order\ar\CartItem::ITEM_STATUS_INVALID => Yii::t('app', '无效')
	]
	,'od_type' => [
		\common\models\order\ar\Order::OD_TYPE_GOODS => Yii::t('app', "商品订单")
	]
	,'od_pay_status' => [
		\common\models\order\ar\Order::PAY_STATUS_NOPAY => Yii::t('app', "未支付")
		,\common\models\order\ar\Order::PAY_STATUS_PAYED => Yii::t('app', '已经支付')
	]
	,'od_comment_status' => [
		\common\models\order\ar\Order::COMMENT_STATUS_NOCOMMENT => Yii::t('app', '没有评论')
		,\common\models\order\ar\Order::COMMENT_STATUS_COMMENTED => Yii::t('app', '已经评论')
	]
	,'od_refund_status' => [
		\common\models\order\ar\Order::RF_STATUS_NORF => Yii::t('app', "没有发生退款"),
		\common\models\order\ar\Order::RF_STATUS_CUSTOMER_SUBMIT => Yii::t('app', "客户提交退款申请")
	]
	,'od_status' => [
		\common\models\order\ar\Order::STATUS_SUBMIT => Yii::t('app', "提交状态")
		, \common\models\order\ar\Order::STATUS_C_PAYED=> Yii::t('app', '用户已经付款')
	]
	,'od_logistics_status' => [
		\common\models\order\ar\Order::LG_STATUS_INIT => Yii::t('app', "初始状态")
	]
	,'od_pay_mode' => [
		\common\models\order\ar\Order::MODE_FULL_ONLINE_PAY => Yii::t('app', "付款模式")
	]
	,'od_express_status' => [
		\common\models\order\ar\OrderExpress::STATUS_ORDER_INIT => Yii::t('app', "用户已经提交订单")

	]
	,'od_express_target_type' => [
		\common\models\order\ar\OrderExpress::TTYPE_ORDER => Yii::t('app', '消费类型订单物流')
	]
	,'g_atr_opt_img' => [
		0 => Yii::t('app', '不能携带图片')
		,1 => Yii::t('app', '能携带图片')
	]
	,'sms_type' => [
		\common\models\sms\ar\Sms::TYPE_PRIVATE => Yii::t('app', '私发短信'),
		\common\models\sms\ar\Sms::TYPE_BOARD => Yii::t('app', '系统短信'),
	]
	,'sms_provider' => [
		\common\models\sms\ar\Sms::PROVIDER_ALIDY => Yii::t('app', '阿里大于')
	]
    */

];
