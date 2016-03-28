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
        
        $newestVideos = $this->videoManager->getVideosFromDB(0, 16);
        $newestAlbums = $this->photosManager->getAlbumsFromDB(0, 16);
        $popularVideos = $this->analyticsManager->getPopularVideosIds(7, 16);
                
        
        $this->sendResponse(new JsonResponse($response));
    }
    
}
