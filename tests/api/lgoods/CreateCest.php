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

        $i->sendPOST("/lcollect", [
            'ac_name' => '鞋子属性集',
            'attrs' => [
                [
                    'a_name' => '尺寸',
                ],
                [
                    'a_name' => '颜色',
                ]
            ]
        ]);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);

        $i->sendGET("/lcollect/" . $data['ac_id']);
        $i->seeResponseCodeIs(200);
        $i->seeResponseContainsJson([
            'code' => 0
        ]);
        $res = json_decode($i->grabResponse(), true);
        $data = $res['data'];
        Debug::debug($data);
        $attrs  = $data['ac_map'];

        $i->sendPOST("/goods", [
            'g_name' => "鞋子",
            'g_sid' => 0,
            'g_stype' => '',
            'ac_id' => $data['ac_id'],
            'g_options' => [
                ['opt_name' => '37', 'opt_attr_id' => $attrs[0]['a_id']],
                ['opt_name' => '38', 'opt_attr_id' => $attrs[0]['a_id']],
                ['opt_name' => '39', 'opt_attr_id' => $attrs[0]['a_id']],
                ['opt_name' => '40', 'opt_attr_id' => $attrs[0]['a_id']],
                ['opt_name' => '41', 'opt_attr_id' => $attrs[0]['a_id']],
                ['opt_name' => '42', 'opt_attr_id' => $attrs[0]['a_id']],
                ['opt_name' => '黄色', 'opt_attr_id' => $attrs[0]['a_id']],
                ['opt_name' => '黑色', 'opt_attr_id' => $attrs[0]['a_id']],
            ],
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
