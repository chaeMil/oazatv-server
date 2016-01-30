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
    
    public function renderDefault($id = 0, $q = '') {
        
        $page = $id;
        
        $search = $this->searchManager->search($q, 500, $page * 16);
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemsPerPage(16);
        $paginator->setPage($page);
        $paginator->setItemCount(count($search['videos']) + count($search['albums']));
        
        $this->template->search = $search;
        $this->template->paginator = $paginator;
        $this->template->page = $paginator->getPage();
        $this->template->pages = $paginator->getPageCount();
        $this->template->itemsPerPage = $paginator->getItemsPerPage();
        
    }
    
    public function renderInlineSearch($id = '') {
        
        $limit = 7;
        $q = $id;
        $search = $this->searchManager->search($q, $limit);
        $this->template->search = $search;
        $this->template->limit = $limit;
        $this->template->q = $q;
        
    }
    
}
