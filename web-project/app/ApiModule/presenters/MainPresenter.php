<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Nette\Application\Responses\JsonResponse,
 Nette\Database\Context,
 Model\VideoManager,
 Model\PhotosManager,
 Model\AnalyticsManager,
 Model\ArchiveManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class MainPresenter extends BasePresenter {
   
    public function renderDefault() {
        $response = array('apiVersion' => 2.0,
                          'appVersion' => VERSION);
        
        $newestVideos = $this->videoManager->getVideosFromDBtoAPI(0, 16);
        $newestAlbums = $this->photosManager->getAlbumsFromDBtoAPI(0, 16);
        $popularVideosIds = $this->analyticsManager->getPopularVideosIds(7, 16);
        
        $popularVideos = array();
        foreach($popularVideosIds as $video) {
            $popularVideos[] = $this->videoManager->getVideoFromDBtoAPI($video['id']);
        }
                
        $response['newestVideos'] = array();
        foreach($newestVideos as $video) {
            $response['newestVideos'][] = $this->createArchiveItem($video);
        }
        
        $response['newestAlbums'] = array();
        foreach($newestAlbums as $album) {
            $response['newestAlbums'][] = $this->createArchiveItem($album);
        }
        
        $response['popularVideos'] = array();
        foreach($popularVideos as $video) {
            $response['popularVideos'][] = $this->createArchiveItem($video);
        }
        
        
        $this->sendResponse(new JsonResponse($response));
    }
    
}
