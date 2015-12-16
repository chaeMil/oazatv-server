<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Nette\Application\Responses\JsonResponse,
 App\ApiModule\JsonApi,
 Model\VideoManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class ArchivePresenter extends BasePresenter {
   
    public function renderDefault() {
        $db = $this->videoManager->getVideosFromDB(0, 10)->fetchAll();
        
        $videosArray = array();
        
        foreach($db as $video) {
            $videoArray = $video->toArray();
            $videoUrlPrefix = SERVER . "/". VIDEOS_FOLDER . $videoArray[VideoManager::COLUMN_ID] . "/";
            
            $videoArray[VideoManager::COLUMN_MP3_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_MP3_FILE];
            $videoArray[VideoManager::COLUMN_MP4_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_MP4_FILE];
            $videoArray[VideoManager::COLUMN_WEBM_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_WEBM_FILE];
            $videoArray[VideoManager::COLUMN_THUMB_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_THUMB_FILE];
            
            $videosArray[] = $videoArray;
        }
        
        $this->sendResponse(new JsonResponse($videosArray));
    }
}
