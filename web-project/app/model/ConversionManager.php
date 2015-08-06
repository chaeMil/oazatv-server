<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 Model\ServerSettings,
 Model\VideoConvertQueueManager,
 Model\VideoManager;

/**
 * Description of ConversionManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class ConversionManager {
    
    /** @var Nette\Database\Context */
    public static $database;
    private $serverSettings;
    private $queueManager;
    private $videoManager;

    public function __construct(Nette\Database\Context $database, \Model\ServerSettings $serverSettings,
     \Model\VideoConvertQueueManager $queueManager, \Model\VideoManager $videoManager) {
        $this::$database = $database;
        $this->serverSettings = $serverSettings;
        $this->queueManager = $queueManager;
        $this->videoManager = $videoManager;
    }
    
    public function startConversion($queueId) {
        $queueItem = $this->queueManager->getVideoFromQueueByQueueId($queueId);
        //$queueItem->update(array(VideoConvertQueueManager::COLUMN_STATUS => VideoConvertQueueManager::STATUS_CONVERTING));
        $video = $this->videoManager->getVideoFromDB($queueItem->video_id);
        
        //setup bitrate
        switch ($queueItem->target) {
            case VideoManager::COLUMN_MP3_FILE:
                $CONVbitrate = $this->serverSettings->loadValue("mp3_bitrate");
                $CONVextension = ".mp3";
                break;
            case VideoManager::COLUMN_MP4_FILE:
                $CONVbitrate = $this->serverSettings->loadValue("mp4_bitrate");
                $CONVextension = ".mp4";
                break;
            case VideoManager::COLUMN_WEBM_FILE:
                $CONVbitrate = $this->serverSettings->loadValue("webm_bitrate");
                $CONVextension = ".webm";
                break;
        }
        
        //setup input file
        switch ($queueItem->input) {
            case VideoManager::COLUMN_MP3_FILE:
                $CONVinput = $video->mp3_file;
                break;
            case VideoManager::COLUMN_MP4_FILE:
                $CONVinput = $video->mp4_file;
                break;
            case VideoManager::COLUMN_WEBM_FILE:
                $CONVinput = $video->webm_file;
                break;
        }
        
        $CONVthreads = $this->serverSettings->loadValue("conversion_threads");
        $CONVfolder = CONVERSION_FOLDER_ROOT . $video->id . "/";
        $CONVtarget = \App\StringUtils::rand(6).$CONVextension;
        
        dump($CONVbitrate); dump($CONVthreads); dump($CONVfolder); dump($CONVinput); dump($CONVtarget);
        
        
    }
}
