<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SearchPresenter
 *
 * @author chaemil
 */

namespace App\ApiModule;

use Nette,
 Nette\Application\Responses\JsonResponse,
 Model\SearchManager,
 Nette\Database\Context;

class SearchPresenter extends BasePresenter {
    
    public function renderDefault($id, $limit) {
        
        if ($limit == null) {
            $limit = 5;
        }
        
        $input = $id;
        
        $response = $this->searchManager->search($input, 0, $limit, true);
                       
        $jsonArray['search'] = $response;
        
        $this->sendJson($jsonArray);
        
    }
    
}
