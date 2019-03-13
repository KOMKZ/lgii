<?php
namespace banner;
use \ApiTester;
use Codeception\Util\Debug;


class CreateLbannerCest
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
        $i->setAuthHeader();$i->sendPOST("/lfile", [
            'file_category' => 'pub_img',
        ], [
            'file' => codecept_data_dir() . '1.png' ,
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $file = $res['data'];
        Debug::debug($file);

        $i->setAuthHeader();$i->sendPOST("/lbanner", [
            'b_img_id' => $file['file_query_id'],
            'b_img_app' => 1,
            'b_img_module' => 1,
            'b_reffer_link' => 'http://www.baidu.com',
            'b_reffer_label' => '百度',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $cls = $res['data'];
        Debug::debug($cls);





    }
}
