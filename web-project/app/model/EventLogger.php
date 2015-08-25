<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

/**
 * Description of EventLogger
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class EventLogger {
    
    const
        DEFAULT_LOG = 'log/default.txt',
        AUTH_LOG = 'log/auth.log',
        ACTIONS_LOG = 'log/actions.log',
        CONVERSION_LOG = 'log/conversions.log';
    
    public static function log($message, $file) {
        if ($file != NULL) {
            if (file_exists($file)) {
                $now = date("Y-m-d H:i:s");   
                file_put_contents($file, $now.' :: '.$message."\n", FILE_APPEND);
            }
        }
    }
}
