<?php
namespace cart;
use \ApiTester;
use Codeception\Util\Debug;


class ListCest
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
            'g_attr_level' => 'all',
            'per-page' => 100,
            'g_cls_id' => 6
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = $i->grabResponse();
        Debug::debug(json_decode($res, true));
    }
}
