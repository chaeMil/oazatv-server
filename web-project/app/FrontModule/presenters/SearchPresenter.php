<?php

namespace App\FrontModule;

use Model\UserManager;
use Nette,
Nette\Database\Context,
Model\SearchManager,
Model\CategoriesManager;

class SearchPresenter extends BasePresenter {
    
    public function renderDefault($page = 0, $q = '') {
        
        $limit = 32;
        $search = $this->searchManager->search($q, 0, 999, $this->lang);
        
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
        
        $this->template->categoriesManager = $this->categoriesManager;
        $this->template->categories = $this->categoriesManager
                ->getLocalizedCategories($this->lang);
        
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
        
        $limit = 15;
        $q = $id;
        $inputArray = explode(' ', str_replace('#', '', $q));
        $search = $this->searchManager->search($q, 0, $limit, $this->lang);
        $this->template->search = $search;
        $this->template->limit = $limit;
        $this->template->q = $q;
        $this->template->inputArray = $inputArray;
        
    }
    
    private function sortItemsByDate($a, $b) {
	if($a['date'] == $b['date']){ 
            return 0 ;
        }
	return ($a['date'] > $b['date']) ? -1 : 1;
    }
    
}
