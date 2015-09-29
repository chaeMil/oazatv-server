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
        $this->template->newestVideos = $this->videoManager->getVideosFromDB(0, 10);
        $this->template->lang = $this->lang;
        dump($this->videoManager->createLocalizedVideoObject($this->lang, 
                $this->videoManager->getVideoFromDB(43)));
    }
    
}
