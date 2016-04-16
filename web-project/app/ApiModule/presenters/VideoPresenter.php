<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Model\VideoManager,
 Model\AnalyticsManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoPresenter extends BasePresenter {
   
    public function actionDefault($id) {
        $hash = $id;
        
        $video = $this->videoManager->getVideoFromDBbyHash($hash);
        
        if ($video != false) {

            $videoArray = $video->toArray();
            
            $videoUrlPrefix = SERVER . "/". VIDEOS_FOLDER . $videoArray[VideoManager::COLUMN_ID] . "/";
            
            $videoArray[VideoManager::COLUMN_MP3_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_MP3_FILE];
            $videoArray[VideoManager::COLUMN_MP4_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_MP4_FILE];
            $videoArray[VideoManager::COLUMN_WEBM_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_WEBM_FILE];
            $videoArray[VideoManager::COLUMN_THUMB_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_THUMB_FILE];
            
            $jsonArray['video'] = $videoArray;
            
            $this->sendJson($jsonArray);
        } else {
            
        $this->createJsonError('videoFileNotFound', 
                Nette\Http\Response::S404_NOT_FOUND, 
                "Video neexistuje", 
                "This video does not exist");
        }   
    }
    
    public function actionCountView($id) {
        $hash = $id;
        $video = $this->videoManager->getVideoFromDBbyHash($hash);
        $videoId = $video['id'];
        $this->videoManager->countView($videoId);
        $this->analyticsManager->countVideoView($videoId, AnalyticsManager::API);
        $this->analyticsManager->addVideoToPopular($videoId);
        
        $this->sendHTTPResponse(Nette\Http\Response::S200_OK);
    }
}
