<?php
namespace cart;
use \ApiTester;
use Codeception\Util\Debug;


class CreateCest
{
    public function _before(ApiTester $I)
    {
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $i)
    {
        $i->sendPOST("/lfile", [
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


        $i->sendPOST("/lcollect", [
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

        $i->sendGET("/lcollect/" . $data['ac_id']);
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
        $i->sendPOST("/goods", [
            'g_name' => "鞋子",
            'g_sid' => 0,
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
                [$sizeId => 37,  $colorId=> 'yellow', 'price' => 1, 'is_master' => 1],
                [$sizeId  => 37, $colorId => 'black', 'price' => 2],
                [$sizeId => 38,  $colorId => 'yellow', 'price' => 3],
                [$sizeId  => 38, $colorId => 'black', 'price' => 4],
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


        $i->sendGET("/goods/" . $data['g_id'], [
            'field_level' => 'all',

        ]);
        $res = json_decode($i->grabResponse(), true);
        $goods = $res['data'];
        Debug::debug($goods);

        $i->sendPOST("/lsale-rule", [
            'sr_name' => '限定时间8折',
            'sr_start_at' => time(),
            'sr_end_at' => time() + 3600,
            'sr_caculate_type' => 1,
            'sr_caculate_params' => "8.0",
            "sr_object_id" => $goods['g_id'],
            "sr_object_type" => 1,
            'sr_usage_intro' => '限定时间8折',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $rule = $res['data'];
        Debug::debug($rule);

        $i->sendPOST("/lsale-rule", [
            'sr_name' => '限定时间7折',
            'sr_start_at' => time(),
            'sr_end_at' => time() + 3600,
            'sr_caculate_type' => 1,
            'sr_caculate_params' => "7.0",
            "sr_object_id" => $goods['goods_skus'][0]['sku_id'],
            "sr_object_type" => 2,
            'sr_usage_intro' => '限定时间7折',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $rule = $res['data'];
        Debug::debug($rule);

        $i->sendPUT("/lsale-rule/" . $rule['sr_id'], [
            'sr_name' => '限定时间7折!',
            'sr_start_at' => time(),
            'sr_end_at' => time() + 7200,
            'sr_usage_intro' => '限定时间7折!',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $rule = $res['data'];
        Debug::debug($rule);
    }
}
