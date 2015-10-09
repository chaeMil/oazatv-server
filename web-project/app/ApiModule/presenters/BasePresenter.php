<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 App\ApiModule\JsonApi,
 Model\VideoManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class BasePresenter extends \Nette\Application\UI\Presenter {
    
    private $database;
    private $videoManager;
    
    public function __construct(Nette\Database\Context $database,
            VideoManager $videoManager) {
        
        $this->database = $database;
        $this->videoManager = $videoManager;
        
    }
}
