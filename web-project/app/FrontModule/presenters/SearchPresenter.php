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
    
    public function renderDefault($page = 0, $q = '') {
        
        $limit = 32;
        $search = $this->searchManager->search($q, 999);
        
        if(isset($search['videos']) || isset($search['albums'])) {
            if(count($search['videos']) != 0 || count($search['albums'] != 0)) {

                $mergedSearch = array_merge($search['videos'], $search['albums']);
                usort($mergedSearch, array($this, 'sortItemsByDate'));

                $searchPage = $page;
                if ($page != 0) {
                    $searchPage = $page -1;
                }
                $mergedLimitedSearch = array_slice($mergedSearch, $searchPage * $limit, $limit);
                
                $paginator = new Nette\Utils\Paginator;
                $paginator->setItemsPerPage($limit);
                $paginator->setPage($page);
                $paginator->setItemCount(count($mergedSearch));
            }
        }
        
        if (isset($mergedLimitedSearch)) {
            $this->template->q = $q;
            $this->template->search = $mergedLimitedSearch;
            $this->template->paginator = $paginator;
            $this->template->page = $paginator->getPage();
            $this->template->pages = $paginator->getPageCount();
            $this->template->itemsPerPage = $paginator->getItemsPerPage();
        }
        
    }
    
    public function renderInlineSearch($id = '') {
        
        $limit = 7;
        $q = $id;
        $search = $this->searchManager->search($q, $limit, 0, $this->lang);
        $this->template->search = $search;
        $this->template->limit = $limit;
        $this->template->q = $q;
        
    }
    
    private function sortItemsByDate($a, $b) {
	if($a['date'] == $b['date']){ return 0 ; }
	return ($a['date'] > $b['date']) ? -1 : 1;
}
    
}
