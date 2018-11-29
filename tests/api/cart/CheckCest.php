<?php
namespace cart;
use \ApiTester;
use Codeception\Util\Debug;


class ListCest
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
    private function installGoods(ApiTester $i)
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
        'g_name' => "鞋子",
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
            [$sizeId => 37,  $colorId=> 'yellow', 'price' => 1, 'is_master' => 1],
            [$sizeId  => 37, $colorId => 'black', 'price' => 1],
            [$sizeId => 38,  $colorId => 'yellow', 'price' => 1],
            [$sizeId  => 38, $colorId => 'black', 'price' => 1],
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

    }
    // tests
    public function tryToTest(ApiTester $i)
    {


        $this->installGoods($i);
        $this->installGoods($i);

        $i->setAuthHeader();$i->sendGET("/lgoods", [
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $goodsList = $res['data']['items'];
        Debug::debug($goodsList);

        $i->setAuthHeader();$i->sendPost("/lcart-item", [
        'ci_sku_id' => $goodsList[0]['sku_id'],
        'ci_amount' => 2,
        'ci_belong_uid' => 0,
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendPost("/lcart-item", [
        'ci_sku_id' => $goodsList[1]['sku_id'],
        'ci_amount' => 2,
        'ci_belong_uid' => 0,
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);


        $i->setAuthHeader();$i->sendGET("/lcart-item", []);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $ids = [];
        foreach ($data['items'] as $item){
            $ids[] = $item['ci_id'];
        }

        $i->setAuthHeader();$i->sendPOST("/lorder/check", [
            'type' => 'cart',
        'ids' => $ids
    ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

    }
}
