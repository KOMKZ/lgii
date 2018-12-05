<?php
namespace lsale;
use \ApiTester;
use Codeception\Util\Debug;


class CreateSaleRule02Cest
{
    public function _before(ApiTester $I){ $I->loginAdmin();
    }

    public function _after(ApiTester $I)
    {
    }

    // tests
    public function tryToTest(ApiTester $i)
    {
        $i->setAuthHeader();$i->sendPOST("/lsale-rule", [
            'sr_name' => '满100元减20元',
            'sr_start_at' => time(),
            'sr_end_at' => time() + 3600 * 24,
            'sr_caculate_type' => 2,
            'sr_caculate_params' => "10000,2000",
            "sr_object_id" => 0,
            "sr_object_type" => 4,
            'sr_usage_intro' => '满100元减20元',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendPOST("/lsale-rule", [
            'sr_name' => '满200元减50元',
            'sr_start_at' => time(),
            'sr_end_at' => time() + 3600 * 24,
            'sr_caculate_type' => 2,
            'sr_caculate_params' => "20000,5000",
            "sr_object_id" => 0,
            "sr_object_type" => 4,
            'sr_usage_intro' => '满200元减50元',
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->setAuthHeader();$i->sendPOST("/lsale-rule", [
            'sr_name' => '满300元减80元',
            'sr_start_at' => time(),
            'sr_end_at' => time() + 3600 * 24,
            'sr_caculate_type' => 2,
            'sr_caculate_params' => "30000,8000",
            "sr_object_id" => 0,
            "sr_object_type" => 4,
            'sr_usage_intro' => '满300元减80元',
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
