<?php

namespace App\FrontModule;

use Nette,
 Nette\Application\Responses\JsonResponse,
 Model\LiveStreamManager;

/**
 * 
 * @author Michal Mlejnek <chaemil72@gmail.com>
 */
class LiveStreamPresenter extends BasePresenter {
    
    public $database;
    public $container;
    public $liveStreamManager;

    function __construct(Nette\DI\Container $container,
            Nette\Database\Context $database, 
            LiveStreamManager $liveStreamManager) {
        
        parent::__construct($container, $database);
        
        $this->liveStreamManager = $liveStreamManager;
    }

    public function renderDefault() {
        $this->template->values = $this->liveStreamManager->loadValues();
    }
    
    public function actionAjaxRefresh() {
        $values = $this->liveStreamManager->loadValues();
        $this->sendResponse(new JsonResponse($values));
    }
    
}
    