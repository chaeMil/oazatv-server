<?php

namespace App\FrontModule;

use Model\PhotosManager;
use Model\UserManager;
use Nette,
Nette\Database\Context,
Model\ArchiveManager,
Model\CategoriesManager,
Model\VideoManager,
Model\ArchiveMenuManager;


class ArchivePresenter extends BasePresenter {
    
    public function renderDefault() {
        $this->redirect('Archive:Page');
    }
    
    public function renderPage($id = 1) {
        
        $page = $id;
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($this->archiveManager->countArchive());
        $paginator->setItemsPerPage(32);
        $paginator->setPage($page);

        $archive = $this->archiveManager
                ->getVideosAndPhotoAlbumsFromDB($paginator->getOffset(), 
                        $paginator->getItemsPerPage(), 
                        $this->lang);

        $this->getBasicVariables($archive, $paginator);
        $this->template->paginationBaselink = $this->link('Page');
    }
    
    public function renderCategory($id, $attr = 1) {

        $category = $this->categoriesManager->getLocalizedCategory($id, $this->lang);
        $itemsPerPage = 32;
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemsPerPage($itemsPerPage);
        $paginator->setPage($attr);
        
        $videos = $this->videoManager
                ->getVideosFromDBbyCategory($category['id'], 
                        $paginator->getOffset(),
                        $paginator->getItemsPerPage());
        
        $videosForCount = $this->videoManager
                ->getVideosFromDBbyCategory($category['id'], 
                        0,
                        $this->archiveManager->countArchive());

        $paginator->setItemCount(sizeof($videosForCount));
        
        $localizedVideos = array();
        foreach($videos as $video) {
            $localizedVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video);
        }

        $this->getBasicVariables($localizedVideos, $paginator);
        $this->template->category = $category;
        $this->template->paginationBaselink = $this->link('Category').$category['id'].'/';
    }

    public function renderVideosWithSubtitles($id) {
        $this->setView('page');

        $page = $id;
        $itemsPerPage = 32;

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemsPerPage($itemsPerPage);
        $paginator->setPage($page);

        $videos = $this->videoManager->getVideosWithSubtitles($paginator->getOffset(),
            $paginator->getItemsPerPage());

        $itemsForCount = $this->videoManager->getVideosWithSubtitles(0, 9999);

        $paginator->setItemCount(sizeof($itemsForCount));

        $localizedVideos = array();
        foreach($videos as $video) {
            $localizedVideos[] = $this->videoManager
                ->createLocalizedVideoObject($this->lang, $video);
        }

        $this->getBasicVariables($localizedVideos, $paginator);
        $this->template->activeMenu = array('id' => 'videosWithSubtitles');
        $this->template->paginationBaselink = $this->link('VideosWithSubtitles');
    }

    public function renderAlbums($id) {
        $this->setView('page');

        $page = $id;
        $itemsPerPage = 32;

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemsPerPage($itemsPerPage);
        $paginator->setPage($page);

        $items = $this->photosManager->getAlbumsFromDB($paginator->getOffset(),
            $paginator->getItemsPerPage());

        $itemsForCount = $this->photosManager->getAlbumsFromDB(0, 9999);

        $paginator->setItemCount(sizeof($itemsForCount));

        $localizedAlbums = array();
        foreach($items as $album) {
            $localizedAlbums[] = $this->photosManager
                ->createLocalizedAlbumThumbObject($this->lang, $album);
        }

        $this->getBasicVariables($localizedAlbums, $paginator);
        $this->template->activeMenu = array('id' => 'albums');
        $this->template->paginationBaselink = $this->link('Albums');
    }
    
    public function renderMenu($id, $attr = 1) {

        $tags = $id;
        $itemsPerPage = 32;

        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemsPerPage($itemsPerPage);
        $paginator->setPage($attr);

        $menu = $this->archiveMenuManager->getMenuFromDBByTags($tags);

        $items = $this->archiveManager
                ->getVideosAndPhotoAlbumsFromDBByTags($paginator->getOffset(),
                        $paginator->getItemsPerPage(),
                        $this->lang,
                        $tags);

        $itemsForCount = $this->archiveManager
                ->getVideosAndPhotoAlbumsFromDBByTags(0,
                        $this->archiveManager->countArchive(),
                        $this->lang,
                        $tags);

        $paginator->setItemCount(sizeof($itemsForCount));

        $this->getBasicVariables($items, $paginator);
        $this->template->activeMenu = $menu;
    }

    /**
     * @param $archive
     * @param $paginator
     */
    private function getBasicVariables($archive, $paginator) {
        $this->template->archiveItems = $archive;
        $this->template->paginator = $paginator;
        $this->template->page = $paginator->getPage();
        $this->template->pages = $paginator->getPageCount();
        $this->template->archiveMenu = $this->archiveMenuManager->getLocalizedMenus($this->lang);
        $this->template->categories = $this->categoriesManager->getLocalizedCategories($this->lang);
        $this->template->archiveMenuManager = $this->archiveMenuManager;
        $this->template->videosWithSubtitlesCount = sizeof($this->videoManager->getVideosWithSubtitles(0, 9999));
        $this->template->albumsCount = sizeof($this->photosManager->getAlbumsFromDB(0, 9999));
    }

}
