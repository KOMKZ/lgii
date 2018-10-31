<?php
function hasError($I){
    $data = json_decode($I->grabResponse(), true);
    return !$data || $data['code'] != 0;
}
function outputRes($I){
    $data = json_decode($I->grabResponse(), true);
    $data && $I->debug($data);
}
function getData($I){
    $res = json_decode($I->grabResponse(), true);
    return $res['data'];
};

$i = new AcceptanceTester($scenario);


$i->sendPOST("/c-order", [
    'co_customer_uid' => '7260',
    'co_buy_type' => 'cpy',
    'co_from' => 'cart',
    'buy_course_list' => [
        ['course_code' => "cde12345", 'buy_person_num' => 1],
        ['course_code' => "cde123456", 'buy_person_num' => 1],
    ]
]);
$i->seeResponseCodeIs(200);
$i->seeResponseContainsJson([
    'code' => 0
]);
$order = getData($i);