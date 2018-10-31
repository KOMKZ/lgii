<?php
namespace lfile\models\drivers;

use lfile\models\ar\File;

/**
 *
 */
interface SaveMediumInterface
{
    public function save(File $file);
    public function buildFileUrl(File $file, $params = []);
    public function saveByCopy(File $targetFile, File $originFile);
}
