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
    
    private $searchManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, SearchManager $searchManager) {
        
        parent::__construct($container, $database);
        $this->searchManager = $searchManager;
    }
    
    public function renderDefault($id, $limit) {
        
        if ($limit == null) {
            $limit = 5;
        }
        
        $input = $id;
        
        $response = $this->searchManager->search($input, $this->lang, $limit, 0, true);
                       
        $this->sendResponse(new JsonResponse($response));
        
    }
    
}
