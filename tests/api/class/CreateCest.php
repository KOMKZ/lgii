<?php
namespace cart;
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
        $i->sendPOST("/lfile", [
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

        $i->sendPOST("/lclassification", [
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


        $i->sendPOST("/lclassification", [
            'g_cls_name' => '男装',
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

        $i->sendPOST("/lclassification", [
            'g_cls_name' => '男士外套',
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

        $i->sendPOST("/lclassification", [
            'g_cls_name' => '男士牛仔',
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

        $i->sendPOST("/lclassification", [
            'g_cls_name' => '女装',
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


        $i->sendPOST("/lclassification", [
            'g_cls_name' => '女士外套',
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

        $i->sendPOST("/lclassification", [
            'g_cls_name' => '女士牛仔',
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

    }
}
