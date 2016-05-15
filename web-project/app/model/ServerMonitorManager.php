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

    public function getTotalDataDiskSpace() {
        return exec('df -aT | grep '.DATA_MOUNTPOINT.' | head -n1 | awk \'{print $3}\'');
    }
    
    public function getUsedDataDiskSpace() {
        return exec('df -aTh | grep '.DATA_MOUNTPOINT.' | head -n1 | awk \'{print $4}\'');
    }
    
    public function getFreeDataDiskSpace() {
        return exec('df -aTh | grep '.DATA_MOUNTPOINT.' | head -n1 | awk \'{print $5}\'');
    }
    
    public function getUsedDataDiskPercent() {
        return exec('df -aTh | grep '.DATA_MOUNTPOINT.' | head -n1 | awk \'{print $6}\'');
    }
    
}
