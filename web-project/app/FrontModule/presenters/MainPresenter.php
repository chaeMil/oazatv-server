<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\VideoManager,
Model\PhotosManager,
Model\AnalyticsManager,
Model\CategoriesManager;


class MainPresenter extends BasePresenter {
    
    private $videoManager;
    private $photosManager;
    private $analyticsManager;
    private $categoriesManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager,
            PhotosManager $photosManager,
            AnalyticsManager $analyticsManager,
            CategoriesManager $categoriesManager) {
        
        parent::__construct($container, $database);
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->analyticsManager = $analyticsManager;
        $this->categoriesManager = $categoriesManager;
    }
    
    public function renderDefault() {
        
        $newestVideos = $this->videoManager->getVideosFromDB(0, 8);
        $templateNewestVideos = null;
        
        foreach($newestVideos as $video) {
            $templateNewestVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video);
        }
        
        $newestAlbums = $this->photosManager->getAlbumsFromDB(0, 8);
        $templateNewestAlbums = null;
        
        foreach($newestAlbums as $album) {
            $templateNewestAlbums[] = $this->photosManager
                    ->createLocalizedAlbumThumbObject($this->lang, $album);
        }
        
        $popularVideos = $this->analyticsManager->getPopularVideosIds(7, 8);
        
        $templatePopularVideos = null;
        
        if($popularVideos != null) {
            foreach($popularVideos as $video) {
                $dbVideo = $this->videoManager->getVideoFromDB($video);
                $templatePopularVideos[] = $this->videoManager
                        ->createLocalizedVideoObject($this->lang, $dbVideo);
            }
        }
        
        $this->template->popularVideos = $templatePopularVideos;
        $this->template->newestVideos = $templateNewestVideos;
        $this->template->newestAlbums = $templateNewestAlbums;
        $this->template->lang = $this->lang;
        $this->template->user = $this->getUser();
        $this->template->categories = $this->categoriesManager->getLocalizedCategories($this->lang);
    }
    
}
