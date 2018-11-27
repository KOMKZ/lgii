<?php
namespace course;
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
        return ;
        $i->sendPOST("/course", [
            'course_title' => '安全防爆电器',
            'course_created_at' => time(),
            'course_updated_at' => time(),
            'price_items' => [
                ['version' => 1, 'ext_serv' => 0, 'price' => 1, 'is_master' => 1],
                ['version' => 2, 'ext_serv' => 0, 'price' => 1],
                ['version' => 1, 'ext_serv' => 1, 'price' => 1],
                ['version' => 2, 'ext_serv' => 1, 'price' => 1],
            ]
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = $i->grabResponse();
        Debug::debug(json_decode($res, true));
    }
}
