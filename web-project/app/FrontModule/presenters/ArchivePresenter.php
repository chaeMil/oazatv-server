<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\ArchiveManager,
Model\CategoriesManager,
Model\VideoManager;


class ArchivePresenter extends BasePresenter {
    
    private $archiveManager;
    private $categoriesManager;
    private $videoManager;
    public $lang;
    public $container;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, ArchiveManager $archiveManager,
            CategoriesManager $categoriesManager,
            VideoManager $videoManager) {
        
        parent::__construct($container, $database);
        $this->archiveManager = $archiveManager;
        $this->categoriesManager = $categoriesManager;
        $this->videoManager = $videoManager;
    }
    
    public function renderDefault() {
        $this->redirect('Archive:Page');
    }
    
    public function renderPage($id = 1) {
        
        $page = $id;
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($this->archiveManager->countArchive());
        $paginator->setItemsPerPage(64);
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
    
    public function renderCategory($id, $attr) {

        $category = $this->categoriesManager->getLocalizedCategory($id, $this->lang);
        $itemsPerPage = 64;
        
        $videos = $this->videoManager
                ->getVideosFromDBbyCategory($category['id'], $attr * $itemsPerPage, 999);
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount(sizeof($videos));
        $paginator->setItemsPerPage($itemsPerPage);
        $paginator->setPage($attr);
        
        $localizedVideos = array();
        foreach($videos as $video) {
            $localizedVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video);
        }
        
        $this->template->archiveItems = $localizedVideos;
        $this->template->paginator = $paginator;
        $this->template->page = $paginator->getPage();
        $this->template->pages = $paginator->getPageCount();
        $this->template->category = $category;
        
    }
    
}
