<?php
namespace category;
use \ApiTester;
use Codeception\Util\Debug;


class UpdateClassCest
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

        $i->setAuthHeader();$i->sendPOST("/lclassification", [
            'g_cls_name' => '服装',
            'g_cls_img_id' => $file['file_query_id'],
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $cls = $res['data'];
        Debug::debug($cls);


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

        $i->setAuthHeader();$i->sendPUT("/lclassification/" . $cls['g_cls_id'] , [
            'g_cls_name' => '服装(修改)',
            'g_cls_img_id' => $file['file_query_id'],
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
