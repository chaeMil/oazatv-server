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
Model\SongsManager,
Model\PreachersManager,
Model\CategoriesManager;

/**
 * Description of VideoPreseter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class AudioPresenter extends BasePresenter {

    private $videoManager;
    private $analyticsManager;
    private $songsManager;
    private $preachersManager;
    private $categoriesManager;

    public function __construct(Nette\DI\Container $container,
            Context $database, VideoManager $videoManager,
            AnalyticsManager $analyticsManager, SongsManager $songsManager,
            PreachersManager $preachersManager,
            CategoriesManager $categoriesManager) {

        parent::__construct($container, $database);

        $this->videoManager = $videoManager;
        $this->analyticsManager = $analyticsManager;
        $this->songsManager = $songsManager;
        $this->preachersManager = $preachersManager;
        $this->categoriesManager = $categoriesManager;
    }
    
    public function renderListen($id, $searched) {
        
        // for compatibility
        $this->redirect("Video:watch", $id);
    }

}
