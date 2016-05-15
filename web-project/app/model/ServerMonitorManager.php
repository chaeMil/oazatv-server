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
    
    const
            TABLE_NAME_TEMP = 'log_cpu_temp',
            TABLE_NAME_LOAD = 'log_cpu_load',
            COLUMN_TIMESTAMP = 'timestamp',
            COLUMN_LOAD = 'cpu_load',
            COLUMN_TEMP = 'temp';
    
    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
    }

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
    
    public function getCPUTemp($intVal = false) {
        $exec = exec(CPU_TEMP_COMMAND);
        if ($intVal) {
            return preg_replace('/[^a-zA-Z]/', '', $exec);
        } else {
            return $exec;
        }
    }
    
    public function getCpuLoad() {
        return exec(CPU_LOAD_COMMAND);
    }
    
    public function saveCpuLog() {
        $cpuLoad = $this->getCpuLoad();
        $cpuTemp = $this->getCPUTemp(true);
        
        if (!is_numeric($cpuLoad)) {
            $cpuLoad = 0;
        }
        self::$database->table(self::TABLE_NAME_LOAD)
                ->insert(array(self::COLUMN_LOAD => $cpuLoad));
        
        if (!is_numeric($cpuTemp)) {
            $cpuTemp = 0;
        }
        self::$database->table(self::TABLE_NAME_TEMP)
                ->insert(array(self::COLUMN_TEMP => $cpuTemp));
    }
    
    public function getCpuLog($items = 30) {
        $cpuLoad = self::$database->table(self::TABLE_NAME_LOAD)
                ->select('*')
                ->order(self::COLUMN_TIMESTAMP)
                ->limit($items)
                ->fetchPairs(self::COLUMN_TIMESTAMP, self::COLUMN_LOAD);
        
        $cpuTemp = self::$database->table(self::TABLE_NAME_TEMP)
                ->select('*')
                ->order(self::COLUMN_TIMESTAMP)
                ->limit($items)
                ->fetchPairs(self::COLUMN_TIMESTAMP, self::COLUMN_TEMP);
        
        $cpuLoadOut = array();
        foreach($cpuLoad as $key => $load) {
            $cpuLoadOut['data'][] = array(strtotime($key), $load);
        }
        
        $cpuTempOut = array();
        foreach($cpuTemp as $key => $temp) {
            $cpuTempOut['data'][] = array(strtotime($key), $temp);
        }
        
        return array('temp' => $cpuTempOut, 'load' => $cpuLoadOut);
    }
}
