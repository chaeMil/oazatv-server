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
    public static function resizeImage($imagefile, $dir, $size, $postfix) {
        $image = Image::fromFile($dir."/".$imagefile);
        $image->resize($size,NULL,Image::SHRINK_ONLY);
        $imagefile_without_extension = StringUtils::removeExtensionFromFileName($imagefile);
        $extension = StringUtils::getExtensionFromFileName($imagefile);
        if ($postfix != "") {
            $separator = "_";
        } else {
            $separator = "";
        }
        $image->save($dir."/".$imagefile_without_extension.$separator.$postfix.".".$extension, 
                    80, Image::JPEG);
    }
}
