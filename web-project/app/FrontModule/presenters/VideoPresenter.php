<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\VideoManager,
Model\AnalyticsManager,
Model\SongsManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoPresenter extends BasePresenter {

    private $videoManager;
    private $analyticsManager;
    private $songsManager;

    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager,
            AnalyticsManager $analyticsManager, SongsManager $songsManager) {

        parent::__construct($container, $database);

        $this->videoManager = $videoManager;
        $this->analyticsManager = $analyticsManager;
        $this->songsManager = $songsManager;
    }

    public function renderWatch($id) {
        $hash = $id; //id only in router, actualy its hash
        $video = $this->videoManager->getVideoFromDBbyHash($hash);

        $tags = explode(",",$video['tags']);
        $tagsWithSongs = $this->songsManager->parseTagsAndReplaceKnownSongs($tags);
        $this->template->tags = $tagsWithSongs;

        $httpResponse = $this->container->getByType('Nette\Http\Response');

        $watchedCookie = $this->getHttpRequest()->getCookie($hash);

        if (!isset($watchedCookie)) {
            $this->videoManager->countView($video->id);
            $this->analyticsManager->addVideoToPopular($video->id);
            $httpResponse->setCookie($hash, 'watched', '1 hour');
        }

        $this->template->video = $this->videoManager
                ->createLocalizedVideoObject($this->lang, $video);
        
        $this->template->similarVideos = $this->videoManager->findSimilarVideos($video, $this->lang);
    }

}
