<?php
namespace goods;
use \ApiTester;
use Codeception\Util\Debug;


class CollectUpdateCest
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
        $i->sendPOST("/lcollect", [
            'ac_name' => '鞋子属性集',
            'attrs' => [
                [
                    'a_name' => '尺寸',
                    'a_type' => 2,
                ],
                [
                    'a_name' => '颜色',
                    'a_type' => 2,
                ]
            ]
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $collect = $res['data'];
        Debug::debug($res);

        $i->sendPUT("/lcollect/" . $collect['ac_id'], [
            'ac_name' => 'shoes attr collections'
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = $i->grabResponse();
        Debug::debug(json_decode($res, true));
    }
}
