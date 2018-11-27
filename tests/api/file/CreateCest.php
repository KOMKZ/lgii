<?php
namespace file;
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

    private function getResData($I){
        $res = json_decode($I->grabResponse(), true);
        return $res;
    }

    // tests
    public function tryToTest(ApiTester $I){
		$I->sendPOST("/lfile", [
			'file_category' => 'pub_img',
		], [
			'file' => codecept_data_dir() . '/1.png' ,
		]);
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson([
			'code' => 0
		]);
		$res = $this->getResData($I);
		Debug::debug($res);

	}
}
