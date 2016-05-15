<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\FileUtils;

/**
 * Description of ServerMonitorManager
 *
 * @author chaemil
 */
class ServerMonitorManager {

    public function getTotalDataDiskSpace($humanReadable = false) {
        if ($humanReadable) {
            $h = 'h';
        } else {
            $h = '';
        }
        return exec('df -aT'.$h.' | grep '.DATA_MOUNTPOINT.' | head -n1 | awk \'{print $3}\'');
    }
    
    public function getUsedDataDiskSpace($humanReadable = false) {
        if ($humanReadable) {
            $h = 'h';
        } else {
            $h = '';
        }
        return exec('df -aT'.$h.' | grep '.DATA_MOUNTPOINT.' | head -n1 | awk \'{print $4}\'');
    }
    
    public function getFreeDataDiskSpace($humanReadable = false) {
        if ($humanReadable) {
            $h = 'h';
        } else {
            $h = '';
        }
        return exec('df -aT'.$h.' | grep '.DATA_MOUNTPOINT.' | head -n1 | awk \'{print $5}\'');
    }
    
    public function getUsedDataDiskPercent($humanReadable = false) {
        if ($humanReadable) {
            $h = 'h';
        } else {
            $h = '';
        }
        return exec('df -aT'.$h.' | grep '.DATA_MOUNTPOINT.' | head -n1 | awk \'{print $6}\'');
    }
    
    public function getTotalSystemDiskSpace($humanReadable = false) {
        if ($humanReadable) {
            $h = 'h';
        } else {
            $h = '';
        }
        return exec('df -aT'.$h.' | grep '.SYSTEM_MOUNTPOINT.' | head -n1 | awk \'{print $3}\'');
    }
    
    public function getUsedSystemDiskSpace($humanReadable = false) {
        if ($humanReadable) {
            $h = 'h';
        } else {
            $h = '';
        }
        return exec('df -aT'.$h.' | grep '.SYSTEM_MOUNTPOINT.' | head -n1 | awk \'{print $4}\'');
    }
    
    public function getFreeSystemDiskSpace($humanReadable = false) {
        if ($humanReadable) {
            $h = 'h';
        } else {
            $h = '';
        }
        return exec('df -aT'.$h.' | grep '.SYSTEM_MOUNTPOINT.' | head -n1 | awk \'{print $5}\'');
    }
    
    public function getUsedSystemDiskPercent($humanReadable = false) {
        if ($humanReadable) {
            $h = 'h';
        } else {
            $h = '';
        }
        return exec('df -aT'.$h.' | grep '.SYSTEM_MOUNTPOINT.' | head -n1 | awk \'{print $6}\'');
    }
    
}
