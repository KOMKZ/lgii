<?php
namespace user;
use \ApiTester;
use Codeception\Util\Debug;


class CreateUserCest
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
//        $i->setAuthHeader();
        $i->sendPOST("/luser", [
            'u_username' => 'lartik' . time(),
            'password' => 'philips',
            'password_confirm' => 'philips',
            'u_email' => sprintf('784248377%s@qq.com', rand(1111, 9999)),
            'u_auth_status' =>'had_auth',
            'u_status' => 'active',
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
