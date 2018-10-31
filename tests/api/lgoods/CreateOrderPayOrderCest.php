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

        $i->sendGET("/goods");
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $goodsList = $res['data']['items'];
        $orderData = [];
        foreach($goodsList as $goods){
            $orderData[] = [
                'og_sku_id' => $goods['sku_id'],
                'og_total_num' => 1,
                'discount_params' => [],
            ];
        }
        Debug::debug($orderData);


        $i->sendPOST("/lorder", $orderData);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);

        $res = json_decode($i->grabResponse(), true);
        $order = $res['data'];
        Debug::debug($order);

        $i->sendPOST(sprintf("/lorder/%s/trans", $order['od_num']), [

        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->sendPOST(sprintf("/ltrans/%s/pay-order", $data['trs_num']), [
            'pt_pay_type' => 'wxpay',
            'pt_pre_order_type' => 'data',
            'pt_payment_id' => 'wxpay_app'
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

        $i->sendGET(sprintf("/lorder/%s", $order['od_num']));
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);
    }
}
