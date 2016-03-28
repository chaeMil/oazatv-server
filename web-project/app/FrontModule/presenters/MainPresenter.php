<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\VideoManager,
Model\PhotosManager,
Model\AnalyticsManager,
Model\CategoriesManager,
Model\FrontPageManager,
WebLoader\Nette\LoaderFactory;


class MainPresenter extends BasePresenter {
    
    private $videoManager;
    private $photosManager;
    private $analyticsManager;
    private $categoriesManager;
    private $frontPageManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, LoaderFactory $webLoader,
            VideoManager $videoManager,
            PhotosManager $photosManager,
            AnalyticsManager $analyticsManager,
            CategoriesManager $categoriesManager,
            FrontPageManager $frontPageManager) {
        
        parent::__construct($container, $database,$webLoader);
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->analyticsManager = $analyticsManager;
        $this->categoriesManager = $categoriesManager;
        $this->frontPageManager = $frontPageManager;
    }
    
    public function renderDefault() {
        
        //support legacy links
        $params = $this->getHttpRequest()->getQuery();
        if (isset($params['page'])) {
            if ($params['page'] == 'vp') {
                if (isset($params['v'])) {
                    $this->redirect("Video:watch", $params['v']);
                }
            }
            
            if ($params['page'] == 'photo-album') {
                if(isset($params['album'])) {
                    $this->redirect("Album:view", $params['album']);
                }
            }
        }
        
        $this->template->rows = $this->frontPageManager->getRowsFromDB();
        $this->template->frontPageManager = $this->frontPageManager;
        $this->template->blockDefinitions = $this->frontPageManager->getBlocksDefinitions();
        $this->template->lang = $this->lang;
        
        $newestVideos = $this->videoManager->getVideosFromDB(0, 16);
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
        $this->template->categories = $this->categoriesManager
                ->getLocalizedCategories($this->lang);
    }
    
}
