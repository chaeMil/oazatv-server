<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette\Application\Responses\JsonResponse;

/**
 * Description of PingAlivePresenter
 *
 * @author chaemil
 */
class PingAlivePresenter extends BasePresenter {
    
    public function actionDefault($oazaUserId, $ip, $os, $browser, $page) {
        if ($this->analyticsManager->updateAliveUser($oazaUserId, $ip, $os, $browser, $page)) {
            $this->sendResponse(new JsonResponse(array("ok")));
        } else {
            $this->sendResponse(new JsonResponse(array("error")));
        }
    }
}
