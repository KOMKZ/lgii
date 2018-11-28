<?php


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;
    public $jwt = "";
    /**
     * Define custom actions here
     */

    public function setAuthHeader(){
        $this->haveHttpHeader("Authorization", "Bearer " . $this->jwt);
    }
    public function loginNormal(){
        $this->sendPOST('/auth/login', [
            'u_email' => '784248378@qq.com',
            'password' => '123456',
            'type' => 'token'
        ]);
        $res = json_decode($this->grabResponse(), true);
        $this->jwt = $res['data']['jwt'];
    }
    public function loginAdmin(){
        $this->sendPOST('/auth/login', [
            'u_email' => '784248377@qq.com',
            'password' => '123456',
            'type' => 'token'
        ]);
        $res = json_decode($this->grabResponse(), true);
        $this->jwt = $res['data']['jwt'];
    }
}
