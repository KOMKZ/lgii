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


        $i->sendGET("/goods", [
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $goodsList = $res['data']['items'];
        Debug::debug($goodsList);

        $i->sendPost("/cart-item", [
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





    }
}
