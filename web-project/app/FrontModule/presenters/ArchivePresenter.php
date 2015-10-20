<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\ArchiveManager;


class ArchivePresenter extends BasePresenter {
    
    private $archiveManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, ArchiveManager $archiveManager) {
        
        parent::__construct($container, $database);
        $this->archiveManager = $archiveManager;
    }
    
    
    public function renderDefault() {
        $archive = $this->archiveManager->getVideosAndPhotoAlbumsFromDB(0, 16, $this->lang);
        $this->template->archiveItems = $archive;
    }
    
}
