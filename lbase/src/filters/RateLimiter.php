<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 18-11-27
 * Time: 上午10:38
 */
namespace lbase\filters;

use Yii;
use yii\filters\RateLimitInterface;

class RateLimiter extends \yii\filters\RateLimiter{
    public $rateLimit = 1;
    public $rateLimitPer = 1;
    public $ignoreIps = [];
    public function beforeAction($action)
    {

        if ($this->user === null && Yii::$app->getUser()) {
            $this->user = Yii::$app->getUser()->getIdentity(false);
        }
        if(!$this->user){
            $ip = Yii::$app->request->getRemoteIp();
            $notCheck = false;
            foreach ($this->ignoreIps as $filter) {
                if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                    $notCheck = true;
                    break;
                }
            }
            if(!$notCheck){
                $ipUser = new IpRateLimitUser();
                $ipUser->rateLimit = $this->rateLimit;
                $ipUser->rateLimitPer = $this->rateLimitPer;
                $ipUser->ip = $ip;
                $this->user = $ipUser;
            }
        }
        if ($this->user instanceof RateLimitInterface) {
            Yii::debug('Check rate limit', __METHOD__);
            $this->checkRateLimit($this->user, $this->request, $this->response, $action);
        } elseif ($this->user) {
            Yii::info('Rate limit skipped: "user" does not implement RateLimitInterface.', __METHOD__);
        } else {
            Yii::info('Rate limit skipped: user not logged in.', __METHOD__);
        }

        return true;
    }
}