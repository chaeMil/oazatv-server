<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Nette\Application\Responses\JsonResponse;
/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class MainPresenter extends BasePresenter {
   
    public function renderDefault() {
        $response = array('apiVersion' => 2.0,
                          'serverVersion' => VERSION);
        
        $newestVideos = $this->videoManager->getVideosFromDBtoAPI(0, 16);
        $newestAlbums = $this->photosManager->getAlbumsFromDBtoAPI(0, 16);
        $popularVideosIds = $this->analyticsManager->getPopularVideosIds(7, 16);
        $featuredItems = $this->frontPageManager->getFeaturedItems();
        
        $popularVideos = array();
        if (isset($popularVideosIds)) {
            foreach($popularVideosIds as $videoId) {
                $popularVideos[] = $this->videoManager->getVideoFromDBtoAPI($videoId);
            }
        }
        
        $response['newestVideos'] = array();
        if (isset($newestVideos)) {
            foreach($newestVideos as $video) {
                if ($video != null) {
                    $response['newestVideos'][] = $this->createArchiveItem($video);
                }
            }
        }
        
        $response['newestAlbums'] = array();
        if (isset($newestAlbums)) {
            foreach($newestAlbums as $album) {
                if ($album != null) {
                    $response['newestAlbums'][] = $this->createArchiveItem($album);
                }
            }
        }
        
        $response['popularVideos'] = array();
        if (isset($popularVideos)) {
            foreach($popularVideos as $video) {
                if ($video != null) {
                    $response['popularVideos'][] = $this->createArchiveItem($video);
                }
            }
        }     
        
        $response['featured'] = array();
        if (isset($featuredItems)) {
            foreach($featuredItems as $item) {
                if ($item != null) {
                    $response['featured'][] = $this->createArchiveItem($item);
                }
            }
        } 

        $this->enableCORS();
        $this->sendResponse(new JsonResponse($response));
    }
    
}
