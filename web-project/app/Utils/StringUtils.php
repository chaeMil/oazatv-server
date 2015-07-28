<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;

/**
 * Description of StringUtils
 *
 * @author chaemil
 */
class StringUtils {
    
    public static function timeElapsedString($ptime) {
        $etime = time() - $ptime;

        if ($etime < 1) {
            return '0 sekund';
        }

        $a = array(365 * 24 * 60 * 60 => 'rokem',
            30 * 24 * 60 * 60 => 'měsícem',
            24 * 60 * 60 => 'dnem',
            60 * 60 => 'hodinou',
            60 => 'minutou',
            1 => 'sekundou'
        );
        $a_plural = array('rok' => 'let',
            'měsícem' => 'měsíci',
            'dnem' => 'dny',
            'hodinou' => 'hodinami',
            'minutou' => 'minutami',
            'sekundou' => 'sekundami'
        );

        foreach ($a as $secs => $str) {
            $d = $etime / $secs;
            if ($d >= 1) {
                $r = round($d);
                return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str);
            }
        }
    }
}
