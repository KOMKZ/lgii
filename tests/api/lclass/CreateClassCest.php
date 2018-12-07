<?php
namespace category;
use \ApiTester;
use Codeception\Util\Debug;


class CreateClassCest
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
            'file' => codecept_data_dir() . '/1.png' ,
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $file = $res['data'];
        Debug::debug($file);

        $i->setAuthHeader();$i->sendPOST("/lclassification", [
            'g_cls_name' => '鞋包配饰',
            'g_cls_img_id' => $file['file_query_id'],
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $cls = $res['data'];
        Debug::debug($cls);


        $i->setAuthHeader();$i->sendPOST("/lclassification", [
            'g_cls_name' => '鞋靴',
            'g_cls_img_id' => $file['file_query_id'],
            'g_cls_pid' => $cls['g_cls_id']
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $clsChild = $res['data'];
        Debug::debug($clsChild);

        $i->setAuthHeader();$i->sendPOST("/lclassification", [
            'g_cls_name' => '男鞋',
            'g_cls_img_id' => $file['file_query_id'],
            'g_cls_pid' => $clsChild['g_cls_id']
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $clsChild = $res['data'];
        Debug::debug($clsChild);




    }
}
