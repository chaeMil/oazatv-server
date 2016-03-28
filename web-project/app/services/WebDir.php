<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of WebDir
 *
 * @author chaemil
 */
namespace App\Services;

 class WebDir {

    private $wwwDir;

    public function __construct($wwwDir) {
        $this->wwwDir = $wwwDir;
    }

    public function getPath($fromBaseDir=''){
        return $this->wwwDir.DIRECTORY_SEPARATOR.$fromBaseDir;
    }
}
