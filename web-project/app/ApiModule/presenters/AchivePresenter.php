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
        $response = $this->videoManager->getVideosFromDB(0, 10, VideoManager::COLUMN_DATE);
        
        $this->sendResponse(new JsonResponse($response));
    }
}
