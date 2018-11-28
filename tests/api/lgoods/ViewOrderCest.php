<?php
namespace goods;
use \ApiTester;
use Codeception\Util\Debug;


class ViewOrderCest
{
    public function _before(ApiTester $I){ $I->loginAdmin();
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $i)
    {

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
    }
}
