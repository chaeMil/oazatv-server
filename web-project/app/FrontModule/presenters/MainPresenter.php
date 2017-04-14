<?php

namespace App\FrontModule;

class MainPresenter extends BasePresenter {
    
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
        
        $this->template->categories = $this->categoriesManager
                ->getLocalizedCategories($this->lang);
        
        $this->template->rows = $this->frontPageManager->getRowsFromDB();
        $this->template->frontPageManager = $this->frontPageManager;
        $this->template->blockDefinitions = $this->frontPageManager->getBlocksDefinitions();
        $this->template->lang = $this->lang;
        
        $newestVideos = $this->videoManager->getVideosFromDB(0, 16);
        $templateNewestVideos = null;
        
        foreach($newestVideos as $video) {
            $templateNewestVideos[] = $this->videoManager
                    ->createLocalizedVideoObject($this->lang, $video, $this->getUserId());
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
                        ->createLocalizedVideoObject($this->lang, $dbVideo, $this->getUserId());
            }
        }
        
        $latestVideo = $this->videoManager->getLatestVideoFromDB();
        
        $this->template->popularVideos = $templatePopularVideos;
        $this->template->newestVideos = $templateNewestVideos;
        $this->template->newestAlbums = $templateNewestAlbums;
        $this->template->latestVideo = $this->videoManager
                ->createLocalizedVideoObject($this->lang, $latestVideo, $this->getUserId());
        $this->template->lang = $this->lang;
        $this->template->user = $this->getUser();
        $this->template->videoManager = $this->videoManager;
    }
    
}
