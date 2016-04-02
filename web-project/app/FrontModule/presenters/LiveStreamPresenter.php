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
        $values = $this->liveStreamManager->loadValues();
        $this->template->values = $values;
        $this->template->lang = $this->lang;
        
        switch($values['on_air']) {
            case 'online':
                $this->template->onAir = true;
                break;
            case 'offline':
                $this->template->onAir = false;
                break;
        }
        
        switch($this->lang) {
            case 'cs':
                $this->template->text = $values['bottom_text_cs'];
                break;
            case 'en':
                $this->template->text = $values['bottom_text_en'];
                break;
        }
    }
    
    public function actionAjaxRefresh() {
        $values = $this->liveStreamManager->loadValues();
        $this->sendResponse(new JsonResponse($values));
    }
    
}
    