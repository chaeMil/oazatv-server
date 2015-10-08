<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

use App\StringUtils,
    Nette\Utils\Image;

/**
 * Description of ImageUtils
 *
 * @author chaemil
 */
class ImageUtils {
    public static function resizeImage($dir, $imagefile, $size, $postfix, $outputDir) {
        if (filesize($dir."/".$imagefile) != 0) {
            try{
                $image = Image::fromFile($dir."/".$imagefile);
                $image->resize($size,NULL,Image::SHRINK_ONLY);
                $imagefile_without_extension = StringUtils::removeExtensionFromFileName($imagefile);
                $extension = StringUtils::getExtensionFromFileName($imagefile);
                if ($postfix != "") {
                    $separator = "_";
                } else {
                    $separator = "";
                }
                if (!empty($outputDir)) {
                    $dir = $outputDir;
                }

                switch($extension) {
                    case 'jpg':
                        $image->save($dir."/".$imagefile_without_extension.$separator.$postfix.".".$extension, 
                            80, Image::JPEG);
                        break;
                    case 'png':
                        $image->save($dir."/".$imagefile_without_extension.$separator.$postfix.".".$extension, 
                            100, Image::PNG);
                        break;
            }
        } catch(Exception $e) {
            Debugger::log($e);
            }
        }
    }
}
