<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 Model\VideoManager,
 Model\UserManager;

/**
 * Description of ThumbnailGeneratorModel
 *
 * @author chaemil
 */
class ThumbnailGenerator {
    
    /** @var Nette\Database\Context */
    public static $database;
    private $videoManager;
    private static $userManager;

    public function __construct(Nette\Database\Context $database,
                                \Model\VideoManager $videoManager, UserManager $userManager) {
        $this::$database = $database;
        $this->videoManager = $videoManager;
        $this::$userManager = $userManager;
    }
    
    public function generate($videoId, $userId, $file, $hour, $minute, $second) {
        $video = $this->videoManager->getVideoFromDB($videoId, 2);
        
        $hour = \App\StringUtils::addLeadingZero($hour, 2);
        $minute = \App\StringUtils::addLeadingZero($minute, 2);
        $second = \App\StringUtils::addLeadingZero($second, 2);
        
        $this::$userManager->createUserTempFolder($userId);
        
        $command = PATH_TO_FFMPEG." -ss ".$hour.":".$minute.":".$second." -i "
                .CONVERSION_FOLDER_ROOT.$videoId."/".$file." -r 1 -vframes 1 -y ".
                USER_TEMP_FOLDER.$userId."/thumb_".$videoId."_".$hour."-".$minute."-".$second.".jpg";
        
        //echo($command);
        
        exec($command);
    }
}
