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
        $a_plural = array('rokem' => 'roky',
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
    
    public static function rand($length) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    public static function removeExtensionFromFileName($filename) {
        return preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename);
    }
    
    public static function getExtensionFromFileName($filename) {
        return pathinfo($filename, PATHINFO_EXTENSION);
    }
    
    public static function addLeadingZero($input, $length) {
        return str_pad($input, $length, '0', STR_PAD_LEFT);
    }
    
    public static function formatSQLDate($year, $month, $day) {
        return date('Y-m-d', strtotime($year."-".StringUtils::addLeadingZero($month, 2)."-".StringUtils::addLeadingZero($day, 2)));
    }
    
    public static function formatCzechDate($year, $month, $day) {
        return /*self::czechDay($day)." ".*/$day.". ".self::czechMonth($month)." ".$year;
    }
    
    public static function czechMonth($mesic) {
        static $nazvy = array(1 => 'leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen', 'září', 'říjen', 'listopad', 'prosinec');
        return $nazvy[$mesic];
    }
    
    public static function czechDay($den) {
        static $nazvy = array('neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota');
        return $nazvy[$den];
    }
    
    public static function formatEnglishDate($year, $month, $day) {
        return $month.'/'.$day.'/'.$year;
    }
}