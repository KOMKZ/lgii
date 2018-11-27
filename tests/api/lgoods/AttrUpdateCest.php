<?php
namespace goods;
use \ApiTester;
use Codeception\Util\Debug;


class AttrUpdateCest
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
        $i->sendPOST("/lattr", [
            'a_name' => '尺寸',
            'a_type' => 2,
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $attr = $res['data'];
        Debug::debug($res);

        $i->sendPUT("/lattr/" . $attr['a_id'], [
            'a_name' => 'size'
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = $i->grabResponse();
        Debug::debug(json_decode($res, true));
    }
}
