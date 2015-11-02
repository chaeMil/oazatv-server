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
Model\AnalyticsManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoPresenter extends BasePresenter {
    
    private $videoManager;
    private $analyticsManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager,
            AnalyticsManager $analyticsManager) {
        
        parent::__construct($container, $database);
        
        $this->videoManager = $videoManager;
        $this->analyticsManager = $analyticsManager;
    }
    
    public function renderWatch($id) {
        $hash = $id; //id only in router, actualy its hash
        $video = $this->videoManager->getVideoFromDBbyHash($hash); 
        
        if(!file_exists(VIDEOS_FOLDER.$video['id']."/time-thumbs/time-thumb-0001.jpg")) {
            $this->videoManager->generateVideoTimeThumbs($video['id']);
        }
        
        $httpResponse = $this->container->getByType('Nette\Http\Response');
        
        $watchedCookie = $this->getHttpRequest()->getCookie($hash);
        
        if (!isset($watchedCookie)) {
            $this->videoManager->countView($video->id);
            $this->analyticsManager->addVideoToPopular($video->id);
            $httpResponse->setCookie($hash, 'watched', '1 hour');
        }

        $this->template->video = $this->videoManager
                ->createLocalizedVideoObject($this->lang, $video);
    }
    
}
