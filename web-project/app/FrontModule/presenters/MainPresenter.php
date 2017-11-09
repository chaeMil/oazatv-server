<?php

namespace App\FrontModule;

use Model\ArchiveMenuManager;
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
    private $archiveMenuManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, LoaderFactory $webLoader,
            VideoManager $videoManager,
            PhotosManager $photosManager,
            AnalyticsManager $analyticsManager,
            CategoriesManager $categoriesManager,
            FrontPageManager $frontPageManager,
            ArchiveMenuManager $archiveMenuManager) {
        
        parent::__construct($container, $database,$webLoader);
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->analyticsManager = $analyticsManager;
        $this->categoriesManager = $categoriesManager;
        $this->frontPageManager = $frontPageManager;
        $this->archiveMenuManager = $archiveMenuManager;
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
        
        $newestVideos = $this->videoManager->getVideosFromDB(0, 16);
        $templateNewestVideos = null;
        
        foreach($newestVideos as $video) {
            $templateNewestVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video);
        }
        
        $newestAlbums = $this->photosManager->getAlbumsFromDB(0, 16);
        $templateNewestAlbums = null;
        
        foreach($newestAlbums as $album) {
            $templateNewestAlbums[] = $this->photosManager
                    ->createLocalizedAlbumThumbObject($this->lang, $album);
        }
        
        $popularVideos = $this->analyticsManager->getPopularVideosIds(7, 16);
        
        $templatePopularVideos = null;
        
        if($popularVideos != null) {
            foreach($popularVideos as $video) {
                $dbVideo = $this->videoManager->getVideoFromDB($video);
                $templatePopularVideos[] = $this->videoManager
                        ->createLocalizedVideoObject($this->lang, $dbVideo);
            }
        }
        
        $latestVideo = $this->videoManager->getLatestVideoFromDB();

        $this->template->archiveMenuManager = $this->archiveMenuManager;
        $this->template->archiveMenu = $this->archiveMenuManager->getLocalizedMenus($this->lang);
        $this->template->englishVideosCount = sizeof($this->videoManager->getVideosFromDBbyTagFilter('english', 'czech', 0, 999));
        $this->template->russianVideosCount = sizeof($this->videoManager->getVideosFromDBbyTagFilter('pусский', 'czech', 0, 999));
        $this->template->videosWithSubtitlesCount = sizeof($this->videoManager->getVideosWithSubtitles(0, 9999));
        $this->template->albumsCount = sizeof($this->photosManager->getAlbumsFromDB(0, 9999));
        $this->template->categories = $this->categoriesManager->getLocalizedCategories($this->lang);
        $this->template->rows = $this->frontPageManager->getRowsFromDB();
        $this->template->frontPageManager = $this->frontPageManager;
        $this->template->blockDefinitions = $this->frontPageManager->getBlocksDefinitions();
        $this->template->lang = $this->lang;
        $this->template->popularVideos = $templatePopularVideos;
        $this->template->newestVideos = $templateNewestVideos;
        $this->template->newestAlbums = $templateNewestAlbums;
        $this->template->latestVideo = $this->videoManager->createLocalizedVideoObject($this->lang, $latestVideo);
        $this->template->lang = $this->lang;
        $this->template->user = $this->getUser();
        $this->template->videoManager = $this->videoManager;
    }
    
}
