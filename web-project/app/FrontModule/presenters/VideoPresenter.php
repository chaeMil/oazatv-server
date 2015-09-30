<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\FrontModule;

use Nette,
Nette\Database\Context,
Model\VideoManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoPresenter extends BasePresenter {
    
    public $videoManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager) {
        parent::__construct($container, $database);
        $this->videoManager = $videoManager;
    }
    
    public function renderWatch($id) {
        $hash = $id; //id only in router, actualy its hash
        $video = $this->videoManager->getVideoFromDBbyHash($hash);
        
        $this->videoManager->countView($video->id);
        
        $this->template->video = $this->videoManager
                ->createLocalizedVideoObject($this->lang, $video);
    }
    
}
