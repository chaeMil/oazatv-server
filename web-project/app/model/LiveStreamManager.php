<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LiveStreamManager
 *
 * @author chaemil
 */
namespace Model;
/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class LiveStreamManager{   
    
    private static $configFile = __DIR__.'/../config/live_stream.json';
    
    public function loadValues() {
        return json_decode(file_get_contents(self::$configFile));
    }
    
    public function saveValues($values) {
        $jsonValues = json_encode($values);
        file_put_contents(self::$configFile, $jsonValues);
    }
    
}