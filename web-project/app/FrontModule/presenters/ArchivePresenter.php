<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\ArchiveManager;


class ArchivePresenter extends BasePresenter {
    
    private $archiveManager;
    public $lang;
    public $container;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, ArchiveManager $archiveManager) {
        
        parent::__construct($container, $database);
        $this->archiveManager = $archiveManager;
    }
    
    public function renderDefault() {
        $this->redirect('Archive:Page');
    }
    
    public function renderPage($id = 1) {
        
        $page = $id;
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($this->archiveManager->countArchive());
        $paginator->setItemsPerPage(16);
        $paginator->setPage($page);

        $archive = $this->archiveManager
                ->getVideosAndPhotoAlbumsFromDB($paginator->getOffset(), 
                        $paginator->getItemsPerPage(), 
                        $this->lang);
        
        $this->template->archiveItems = $archive;
        $this->template->paginator = $paginator;
        $this->template->page = $paginator->getPage();
        $this->template->pages = $paginator->getPageCount();
    }
    
}
