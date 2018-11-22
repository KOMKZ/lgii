<?php
namespace lbase;

class ErrorHandler extends \yii\web\ErrorHandler{
    protected function convertExceptionToArray($exception)
    {
        if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
            $exception = new HttpException(500, Yii::t('yii', 'An internal server error occurred.'));
        }

        $array = [
            'data' => null,
            'message' => ($exception instanceof Exception || $exception instanceof ErrorException) ? $exception->getName() : 'Exception'
                         . ':' . $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        if ($exception instanceof HttpException) {
            $array['code'] = $exception->statusCode;
        }
        if(!$array['code']){
            $array['code'] = 500;
        }


        return $array;
    }
}