<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\SearchManager;

class SearchPresenter extends BasePresenter {
    
    public $lang;
    public $container;
    public $searchManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, SearchManager $searchManager) {
        
        parent::__construct($container, $database);
        $this->searchManager = $searchManager;
    }
    
    public function renderInlineSearch($id = '') {
        
        $q = $id;
        $search = $this->searchManager->search($q);
        
    }
    
}
