<?php

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\VideoManager;


class MainPresenter extends BasePresenter {
    
    public $videoManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager) {
        parent::__construct($container, $database);
        $this->videoManager = $videoManager;
    }
    
    public function renderDefault() {
        $newestVideos = $this->videoManager->getVideosFromDB(0, 10);
        $templateNewestVideos;
        
        foreach($newestVideos as $video) {
            $templateNewestVideos[] = $video;
        }
        
        $this->template->newestVideos = $templateNewestVideos;
        $this->template->lang = $this->lang;
    }
    
}
