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
        $res = $i->grabResponse();
        Debug::debug(json_decode($res, true));
    }
}
