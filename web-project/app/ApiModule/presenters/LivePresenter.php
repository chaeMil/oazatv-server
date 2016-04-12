<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette\Application\Responses\JsonResponse;

/**
 * Description of LivePresenter
 *
 * @author chaemil
 */
class LivePresenter extends BasePresenter {
    
    public function renderDefault() {
        $values = $this->liveStreamManager->loadValues();
        $this->sendResponse(new JsonResponse($values));
    }
    
}
