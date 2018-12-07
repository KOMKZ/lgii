<?php
namespace coupon;
use \ApiTester;
use Codeception\Util\Debug;


class DeleteCouponCest
{
    public function _before(ApiTester $I)
    {
        $I->loginAdmin();
    }

    public function _after(ApiTester $I)
    {
    }
    private function installCategory(ApiTester $i){
        $i->setAuthHeader();$i->sendPOST("/lfile", [
            'file_category' => 'pub_img',
        ], [
            'file' => codecept_data_dir() . '/1.png' ,
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $file = $res['data'];
        Debug::debug($file);

        $i->setAuthHeader();$i->sendPOST("/lclassification", [
            'g_cls_name' => '鞋包配饰',
            'g_cls_img_id' => $file['file_query_id'],
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $cls = $res['data'];
        Debug::debug($cls);


        $i->setAuthHeader();$i->sendPOST("/lclassification", [
            'g_cls_name' => '鞋靴',
            'g_cls_img_id' => $file['file_query_id'],
            'g_cls_pid' => $cls['g_cls_id']
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $clsChild = $res['data'];
        Debug::debug($clsChild);

        $i->setAuthHeader();$i->sendPOST("/lclassification", [
            'g_cls_name' => '男鞋',
            'g_cls_img_id' => $file['file_query_id'],
            'g_cls_pid' => $clsChild['g_cls_id']
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $clsChild = $res['data'];
        Debug::debug($clsChild);
        return $clsChild;
    }
    // tests
    public function tryToTest(ApiTester $i)
    {
        $i->setAuthHeader();$i->sendPOST("/lfile", [
        'file_category' => 'pub_img',
    ], [
        'file' => codecept_data_dir() . '/1.png' ,
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $file = $res['data'];
        Debug::debug($file);


        $i->setAuthHeader();$i->sendPOST("/lcollect", [
        'ac_name' => '鞋子属性集',
        'attrs' => [
            [
                'a_name' => '尺寸',
                'a_type' => 2,
            ],
            [
                'a_name' => '颜色',
                'a_type' => 2
            ],
            [
                'a_name' => '产地',
                'a_type' => 3
            ],
            [
                'a_name' => '细节介绍',
                'a_type' => 1
            ],
            [
                'a_name' => '将被置空的属性',
                'a_type' => 1
            ]
        ]
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendGET("/lcollect/" . $data['ac_id']);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);
        $attrs  = $data['ac_map'];

        $sizeId = $attrs[0]['a_id'];
        $colorId = $attrs[1]['a_id'];

        $cls = $this->installCategory($i);

        $i->setAuthHeader();$i->sendPOST("/lgoods", [
        'g_name' => "大梁鞋",
        'g_sid' => 0,
        'g_cls_id' => $cls['g_cls_id'],
        'g_m_img_id' => $file['file_query_id'],
        'g_stype' => '',
        'ac_id' => $data['ac_id'],
        'g_options' => [
            ['opt_name' => '37', 'opt_value' => 37, 'opt_attr_id' => $attrs[0]['a_id']],
            ['opt_name' => '38', 'opt_value' => 38,'opt_attr_id' => $attrs[0]['a_id']],
            ['opt_name' => '黄色', 'opt_value' => "yellow",'opt_attr_id' => $attrs[1]['a_id']],
            ['opt_name' => '黑色', 'opt_value' => 'black', 'opt_attr_id' => $attrs[1]['a_id']],

            ['opt_name' => '法国', 'opt_attr_id' => $attrs[2]['a_id']],
            ['opt_name' => '这里本来是一串富文本介绍的', 'opt_attr_id' => $attrs[3]['a_id']],
            ['opt_name' => '等下我要删除这个属性值的', 'opt_attr_id' => $attrs[4]['a_id']],
        ],
        'price_items' => [
            [$sizeId => 37,  $colorId=> 'yellow', 'price' => 80000, 'is_master' => 1],
            [$sizeId  => 37, $colorId => 'black', 'price' => 80000],
            [$sizeId => 38,  $colorId => 'yellow', 'price' => 80000],
            [$sizeId  => 38, $colorId => 'black', 'price' => 80000],
        ]
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        $attrs = $data['g_attrs'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendGET("/lgoods/" . $data['g_id']);
        $res = json_decode($i->grabResponse(), true);
        $goods = $res['data'];
        Debug::debug($goods);




        $i->setAuthHeader();$i->sendPOST("/lcoupon", [
        'coup_name' => '100元直减券',
        'coup_caculate_type' => 2,
        'coup_caculate_params' => '0,10000',
        'coup_object_id' => 0,
        'coup_object_type' => 4,
        'coup_limit_params' => "",
        'coup_start_at' => time(),
        'coup_end_at' => time() + 100000,
        'coup_usage_intro' => '100元直减券',
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendPOST("/lcoupon", [
        'coup_name' => '满200减20券',
        'coup_caculate_type' => 2,
        'coup_caculate_params' => '20000,2000',
        'coup_object_id' => $goods['g_id'],
        'coup_object_type' => 1,
        'coup_limit_params' => "",
        'coup_start_at' => time(),
        'coup_end_at' => time() + 100000,
        'coup_usage_intro' => '满200减20券',
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendDELETE("/lcoupon/" . $data['coup_id']);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $cls = $res['data'];
        Debug::debug($cls);


    }
}
