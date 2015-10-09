<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Nette\Application\Responses\JsonResponse,
 App\ApiModule\JsonApi;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoPresenter extends BasePresenter {

    private $database;
    private $videoManager;
    
    public function __construct(Nette\Database\Context $database, 
            \Model\VideoManager $videoManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
    }
   
    public function actionDefault($id, $lang) {
        $hash = $id;
        
        $video = $this->videoManager->getVideoFromDBbyHash($hash);
        
        if ($video != false) {
            
            $localizedVideo = $this->videoManager->createLocalizedVideoObject($lang, $video);
            
            dump($localizedVideo); exit;
            
            $response = array('apiVersion' => 2.0,
                          'appVersion' => VERSION);
        
            $this->sendResponse(new JsonResponse($response));
        }
        
        
    }
}
