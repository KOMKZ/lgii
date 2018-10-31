<?php
namespace lfile\models\query;

use yii\base\Object;
use lfile\models\ar\File;

/**
 *
 */
class FileQuery extends Object{
    public static function find(){
        return File::find();
    }
}
