<?php
namespace lbase\helpers;

use yii\helpers\BaseFileHelper;
use trainor\helpers\ExifTool;

/**
 *
 */
class FileHelper extends BaseFileHelper
{
    public static function buildFileSafeName($name){
        return $name;
    }
    public static function caculateFileHash($file){
        return sha1_file($file);
    }
    public static function getFileExtFromName($name){
        $name = basename($name);
        if(false !== ($pos = strrpos($name, '.'))){
            return substr($name, $pos + 1);
        }
        return "";
    }
    public static function getFileExtName($file, $expect = null, $strict = false){
        if(!file_exists($file)){
            return self::getFileExtFromName($file);
        }else{
            $expect = $expect ? $expect : '';
        }
        // return array consists of extension may be
        $mineType = self::getMimeType($expect);
        $candicates = self::getExtensionsByMimeType($mineType);

        if(null !== $expect && in_array($expect, $candicates)){
            return $expect;
        }else{
            if(!$strict){

                return $expect;
            }else{
                $exiftool = new ExifTool($file);
                $ext = $exiftool->getFileExt();
                if($ext == $expect){
                    return $expect;
                }elseif(!empty($candicates)){
                    return array_pop($candicates);
                }else{
                    return "";
                }
            }
        }
    }
    public static function getFileName($file){
        $basename = basename($file);
        $pos = strrpos($file, '.');
        if(false === $pos){
            return $file;
        }else{
            return substr($file, 0, $pos);
        }
    }
    public static function getImgExtByMt($mineType){
        $map = [
            'image/gif' => IMAGETYPE_GIF,
            'image/jpeg' => IMAGETYPE_JPEG,
            'image/png' => IMAGETYPE_PNG,
            'application/x-shockwave-flash' => IMAGETYPE_SWF,
            'image/psd' => IMAGETYPE_PSD,
            'image/bmp' => IMAGETYPE_BMP,
            'image/tiff' => IMAGETYPE_TIFF_II,
            'image/tiff' => IMAGETYPE_TIFF_MM,
            'application/octet-stream' => IMAGETYPE_JPC,
            'image/jp2' => IMAGETYPE_JP2,
            'application/octet-stream' => IMAGETYPE_JPX,
            'application/octet-stream' => IMAGETYPE_JB2,
            'application/x-shockwave-flash' => IMAGETYPE_SWC,
            'image/iff' => IMAGETYPE_IFF,
            'image/vnd.wap.wbmp' => IMAGETYPE_WBMP,
            'image/xbm' => IMAGETYPE_XBM,
            'image/vnd.microsoft.icon' => IMAGETYPE_ICO,
        ];
        return image_type_to_extension($map[$mineType], false);
    }
    public static function checkImgByMt($mineType){
        $imageMineTypes = [
            'image/gif',
            'image/jpeg',
            'image/png',
            'application/x-shockwave-flash',
            'image/psd',
            'image/x-ms-bmp',
            'image/tiff',
            'image/tiff',
            'application/octet-stream',
            'image/jp2',
            'application/octet-stream',
            'application/octet-stream',
            'application/x-shockwave-flash',
            'image/iff',
            'image/vnd.wap.wbmp',
            'image/xbm',
            'image/vnd.microsoft.icon',
        ];
        return in_array($mineType, $imageMineTypes);
    }
}
