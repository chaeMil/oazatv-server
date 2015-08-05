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
        $queueItem->update(array(VideoConvertQueueManager::COLUMN_STATUS => VideoConvertQueueManager::STATUS_CONVERTING));
        $video = $this->videoManager->getVideoFromDB($queueItem->video_id);
        
    }
}
