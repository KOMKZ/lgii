<?php
namespace goods;
use \ApiTester;
use Codeception\Util\Debug;


class CreateOrderRefundCest
{
    public function _before(ApiTester $I){ $I->loginAdmin();
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
    private function installGoods(ApiTester $i){
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

        $i->setAuthHeader();$i->sendGET("/lgoods");
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $goodsList = $res['data']['items'];
        $orderData = [
            'og_list' => []
        ];
        foreach($goodsList as $goods){
            $orderData['order_goods_list'][] = [
                'og_sku_id' => $goods['sku_id'],
                'og_total_num' => 1,
                'discount_params' => [],
            ];
        }
        Debug::debug($orderData);
        $i->setAuthHeader();$i->sendPOST("/lorder", $orderData);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = $i->grabResponse();
        $res = json_decode($i->grabResponse(), true);
        $order = $res['data'];
        Debug::debug($order);

        $i->setAuthHeader();$i->sendPOST(sprintf("/lorder/%s/trans", $order['od_num']), [

        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendPOST(sprintf("/ltrans/%s/pay-order", $data['trs_num']), [
            'pt_pay_type' => 'npay',
            'pt_pre_order_type' => 'data'
//            'pt_pay_type' => 'wxpay',
//            'pt_pre_order_type' => 'data',
//            'pt_payment_id' => 'wxpay_app'
//            'pt_pre_order_type' => 'url',
//            'pt_payment_id' => 'wxpay'
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendGET(sprintf("/lorder/%s", $order['od_num']));
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        // 申请退款
        $i->setAuthHeader();$i->sendPOST("/lrefund", [
            'od_num' => $order['od_num'],
            'og_rf_goods_list' => [
                [
                    'og_id' => $order['order_goods_list'][0]['og_id']
                ],
                [
                    'og_id' => $order['order_goods_list'][0]['og_id']
                ],
            ]
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);

        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        // 管理员同意退款
        $i->setAuthHeader();$i->sendPUT(sprintf("/lrefund/%s/status/agree", $data['rf_num']), [
            'opr_uid' => 1
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
