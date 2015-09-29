<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\VideoManager,
Model\PhotosManager;


class MainPresenter extends BasePresenter {
    
    public $videoManager;
    public $photosManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager,
            PhotosManager $photosManager) {
        parent::__construct($container, $database);
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
    }
    
    public function renderDefault() {
        $newestVideos = $this->videoManager->getVideosFromDB(0, 10);
        
        foreach($newestVideos as $video) {
            $templateNewestVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video);
        }
        
        $newestAlbums = $this->photosManager->getAlbumsFromDB(0, 10);
        
        foreach($newestAlbums as $album) {
            $templateNewestAlbums[] = $this->photosManager
                    ->createLocalizedAlbumThumbObject($this->lang, $album);
        }
        
        $this->template->newestVideos = $templateNewestVideos;
        $this->template->newestAlbums = $templateNewestAlbums;
        $this->template->lang = $this->lang;
    }
    
}
