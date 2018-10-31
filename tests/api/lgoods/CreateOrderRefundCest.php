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
        // 获取商品
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

        // 创建订单
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

        // 创建交易
        $i->sendPOST(sprintf("/ltrans/%s/pay-order", $data['trs_num']), [
            'pt_pay_type' => 'npay',
            'pt_pre_order_type' => 'url',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        // 创建支付单
        $i->sendGET(sprintf("/lorder/%s", $order['od_num']));
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        // 申请退款
        $i->sendPOST("/lrefund", [
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
        $i->sendPUT(sprintf("/lrefund/%s/status/agree", $data['rf_num']), [
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
