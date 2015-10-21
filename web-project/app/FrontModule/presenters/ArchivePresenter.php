<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\ArchiveManager,
IPub\VisualPaginator\Components as VisualPaginator;


class ArchivePresenter extends BasePresenter {
    
    private $archiveManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, ArchiveManager $archiveManager) {
        
        parent::__construct($container, $database);
        $this->archiveManager = $archiveManager;
    }
    
    
    public function renderDefault() {
        
        $visualPaginator = $this['visualPaginator'];
        $paginator = $visualPaginator->getPaginator();
        $paginator->itemsPerPage = 16;
        $paginator->itemCount = $this->archiveManager->countArchive();
        
        
        $archive = $this->archiveManager
                ->getVideosAndPhotoAlbumsFromDB($paginator->offset, 
                        $paginator->itemsPerPage, 
                        $this->lang);
        $this->template->archiveItems = $archive;
        $this->template->paginator = $paginator;
    }
    
}
