<?php
namespace file;
use \ApiTester;

use Codeception\Util\Debug;

class CreateCest
{

    public function _before(ApiTester $I)
    {
        $I->loginAdmin();
//        $I->loginNormal();
    }

    public function _after(ApiTester $I)
    {
    }



    // tests
    public function tryToTest(ApiTester $I){
        $I->setAuthHeader();
		$I->sendPOST("/lfile", [
			'file_category' => 'pub_img',
		], [
			'file' => codecept_data_dir() . '/1.png' ,
		]);
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson([
			'code' => 0
		]);
        $res = json_decode($I->grabResponse(), true);
		Debug::debug($res);

	}
}
