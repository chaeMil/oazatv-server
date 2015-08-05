<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\VideoManager,
 Model\VideoConvertQueueManager;

/**
 * Description of CronPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class CronPresenter extends BasePresenter {
    
    public $database;
    private $videoManager;
    private $queueManager;

    function __construct(Nette\Database\Context $database, 
            VideoManager $videoManager, \Model\VideoConvertQueueManager $queueManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->queueManager = $queueManager;
    }
    
    public function actionCheckVideoConversionQueue() {
        $videoToConvert = $this->queueManager->getFirstVideoToConvert();
        if ($videoToConvert) {
            $videoToConvertFromDB = $this->videoManager->getVideoFromDB($videoToConvert->video_id);
            $this->flashMessage("found video to convert: [".$videoToConvertFromDB->id."] ".
                    $videoToConvertFromDB->name_cs." / ".$videoToConvertFromDB->name_en."  |  conversion: ".
                    $videoToConvert->input." > ".$videoToConvert->target, "info");
        } else {
            if ($this->queueManager->isConvertingNow()) {
                $convertedVideo = $this->queueManager->getCurrentlyConvertedVideo();
                $convertedVideoFromDB = $this->videoManager->getVideoFromDB($convertedVideo->video_id);
                
                $this->flashMessage("now converting video: [".$convertedVideoFromDB->id."] ".
                        $convertedVideoFromDB->name_cs." / ".$convertedVideoFromDB->name_en."  |  conversion: ".
                        $convertedVideo->input." > ".$convertedVideo->target);
            } else {
                $this->flashMessage("nothing to convert and nothing is converting now", "info");
            }
        }
    }
}
