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
 Model\CategoriesManager,
 App\ApiModule\JsonApi,
 Model\FrontPageManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class MainPresenter extends BasePresenter {
    
    private $videoManager;
    private $photosManager;
    private $analyticsManager;
    private $categoriesManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database,
            VideoManager $videoManager,
            PhotosManager $photosManager,
            AnalyticsManager $analyticsManager,
            CategoriesManager $categoriesManager) {
        
        parent::__construct($container, $database);
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->analyticsManager = $analyticsManager;
        $this->categoriesManager = $categoriesManager;
    }
   
    public function renderDefault() {
        $response = array('apiVersion' => 2.0,
                          'appVersion' => VERSION);
        
        
        $this->sendResponse(new JsonResponse($response));
    }
    
}
