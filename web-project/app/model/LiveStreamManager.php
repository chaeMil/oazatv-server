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
    
    const
        CONFIG_FILE = '/../config/live_stream.json';

    public function loadValues() {
        return json_decode(file_get_contents(__DIR__.self::CONFIG_FILE), true);
    }
    
    public function saveValues($values) {
        $jsonValues = json_encode($values);
        file_put_contents(__DIR__.self::CONFIG_FILE, $jsonValues);
    }
    
    
    
}