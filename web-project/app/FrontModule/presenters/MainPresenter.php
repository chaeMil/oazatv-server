<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\VideoManager,
Model\PhotosManager,
Model\AnalyticsManager;


class MainPresenter extends BasePresenter {
    
    private $videoManager;
    private $photosManager;
    private $analyticsManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager,
            PhotosManager $photosManager,
            AnalyticsManager $analyticsManager) {
        
        parent::__construct($container, $database);
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->analyticsManager = $analyticsManager;
    }
    
    public function renderDefault() {
        $newestVideos = $this->videoManager->getVideosFromDB(0, 10);
        $templateNewestVideos = null;
        
        foreach($newestVideos as $video) {
            $templateNewestVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video);
        }
        
        $newestAlbums = $this->photosManager->getAlbumsFromDB(0, 10);
        $templateNewestAlbums = null;
        
        foreach($newestAlbums as $album) {
            $templateNewestAlbums[] = $this->photosManager
                    ->createLocalizedAlbumThumbObject($this->lang, $album);
        }
        
        $popularVideos = $this->analyticsManager->getPopularVideosIds();
        
        $templatePopularVideos = null;
        
        foreach($popularVideos as $video) {
            $dbVideo = $this->videoManager->getVideoFromDB($video);
            $templatePopularVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $dbVideo);
        }
        
        $this->template->popularVideos = $templatePopularVideos;
        $this->template->newestVideos = $templateNewestVideos;
        $this->template->newestAlbums = $templateNewestAlbums;
        $this->template->lang = $this->lang;
    }
    
}
