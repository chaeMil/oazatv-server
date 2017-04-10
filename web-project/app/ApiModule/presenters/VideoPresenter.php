<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Model\UserManager;
use Model\VideoManager;
use Nette,
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

            $videoItem = $video->toArray();
            $videoItem['type'] = 'video';
            $videoItem = $this->createArchiveItem($videoItem);

            $jsonArray['video'] = $videoItem;

            $this->enableCORS();
            $this->sendJson($jsonArray);
        } else {

            $this->enableCORS();
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
        
        $this->sendHTTPResponse(\Nette\Http\Response::S200_OK);
    }

    public function actionSimilar($id) {
        $hash = $id;

        $video = $this->videoManager->getVideoFromDBbyHash($hash);

        if ($video != false) {
            $similarVideos = $this->videoManager->findSimilarVideos($video);
            $similarVideosResponse = array();
            foreach ($similarVideos as $similarVideo) {
                $similarVideosResponse[] = $this->createArchiveItem($similarVideo);
            }
            $this->sendJson(array('videos' => $similarVideosResponse));
        } else {

            $this->enableCORS();
            $this->createJsonError('videoFileNotFound',
                Nette\Http\Response::S404_NOT_FOUND,
                "Video neexistuje",
                "This video does not exist");
        }
    }

    public function actionSaveTime($id, $token, $time) {
        $hash = $id;

        $tokenValid = $this->userManager->validateUserToken($token);

        if ($tokenValid) {
            $video = $this->videoManager->getVideoFromDBbyHash($hash);
            $user = $this->userManager->findByToken($token);

            if ($video) {
                $this->myOazaManager->saveVideoTime($user[UserManager::COLUMN_ID],
                    $video[VideoManager::COLUMN_ID],
                    $time);
                $this->sendJson(array("status" => "ok"));
            } else {
                $this->sendJson(array("status" => "error"));
            }
        }

        $this->sendJson(array("status" => "error", "token_valid" => $tokenValid));
    }

    public function actionGetTime($id, $token) {
        $hash = $id;

        $tokenValid = $this->userManager->validateUserToken($token);

        if ($tokenValid) {
            $video = $this->videoManager->getVideoFromDBbyHash($hash);
            $user = $this->userManager->findByToken($token);

            if ($video) {
                $time = $this->myOazaManager->getVideoTime($user[UserManager::COLUMN_ID],
                    $video[VideoManager::COLUMN_ID]);
                $this->sendJson(array("status" => "ok", "time" => $time));
            } else {
                $this->sendJson(array("status" => "error"));
            }
        }

        $this->sendJson(array("status" => "error", "token_valid" => $tokenValid));
    }
}
