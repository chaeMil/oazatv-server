<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

/**
 * Description of LatteUtils
 *
 * @author chaemil
 */
class LatteUtils {
    
    public function rgbcode($string){
        return '#'.substr(md5($string), 0, 6);
    }
}
