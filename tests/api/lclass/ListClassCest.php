<?php
namespace category;
use \ApiTester;
use Codeception\Util\Debug;


class ListClassCest
{
    public function _before(ApiTester $I)
    {
        $I->loginAdmin();

    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $i)
    {
        $i->setAuthHeader();$i->sendGET("/lclassification");
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = $i->grabResponse();
        Debug::debug(json_decode($res, true));
    }
}
