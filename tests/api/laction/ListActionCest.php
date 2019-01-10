<?php
namespace action;
use \ApiTester;
use Codeception\Util\Debug;


class ListActionCest
{
    public function _before(ApiTester $I)
    {
        $I->loginAdmin();

    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $i){
        $i->setAuthHeader();$i->sendGET("/laction", [

        ], [

        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $file = $res['data'];
        Debug::debug($file);
    }
}