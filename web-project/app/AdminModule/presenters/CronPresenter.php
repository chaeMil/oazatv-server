<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\AdminModule;

use Nette,
 Model\VideoManager,
 Model\VideoConvertQueueManager,
 Model\ConversionManager;

/**
 * Description of CronPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class CronPresenter extends BasePresenter {
    
    public $database;
    private $videoManager;
    private $queueManager;
    private $conversionManager;

    function __construct(Nette\Database\Context $database, 
            VideoManager $videoManager, \Model\VideoConvertQueueManager $queueManager,
            \Model\ConversionManager $conversionManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
        $this->queueManager = $queueManager;
        $this->conversionManager = $conversionManager;
    }
    
    public function actionCheckVideoConversionQueue() {
        
        if ($this->queueManager->isConvertingNow() && $this->queueManager->isFFMPEGRunning()) {
            $convertedVideo = $this->queueManager->getCurrentlyConvertedVideo();
            $convertedVideoFromDB = $this->videoManager->getVideoFromDB($convertedVideo->video_id);

            $this->flashMessage("now converting video: [".$convertedVideoFromDB->id."] ".
                    $convertedVideoFromDB->name_cs." / ".$convertedVideoFromDB->name_en."  |  conversion: ".
                    $convertedVideo->input." > ".$convertedVideo->target);
            
        } elseif($this->queueManager->isConvertingNow() && !$this->queueManager->isFFMPEGRunning()) {
            
            $this->redirect("Cron:ConversionCompleteCallback");
            
        } else {
            
            $videoToConvert = $this->queueManager->getFirstVideoToConvert();
            if ($videoToConvert) {
                $videoToConvertFromDB = $this->videoManager->getVideoFromDB($videoToConvert->video_id);
                $this->flashMessage("found video to convert: [".$videoToConvertFromDB->id."] ".
                        $videoToConvertFromDB->name_cs." / ".$videoToConvertFromDB->name_en."  |  conversion: ".
                        $videoToConvert->input." > ".$videoToConvert->target, "info");
                
                $this->conversionManager->startConversion($videoToConvert->id);
            } else {
                $this->flashMessage("nothing to convert and nothing is converting now", "info");
            }
        }
    }
    
    public function actionConversionCompleteCallback() {
        $convertedVideo = $this->queueManager->getCurrentlyConvertedVideo();
        if (!empty($convertedVideo)) {
            $convertedVideoFromDB = $this->videoManager->getVideoFromDB($convertedVideo->video_id);

            $convertedVideo->update(array(VideoConvertQueueManager::COLUMN_FINISHED_AT => date('Y-m-d H:i:s'),
                                    VideoConvertQueueManager::COLUMN_STATUS => VideoConvertQueueManager::STATUS_FINISHED));
            
            $convertedVideoFromDB->update(array($convertedVideo->target => $convertedVideo->target_filename));
        }
        
        
    }
}
