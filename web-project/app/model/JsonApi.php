<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Model\PhotosManager,
 Model\VideoManager,
 App\StringUtils,
 App\ImageUtils;

/**
 * Description of AlbumsPresenter
 *
 * @author chaemil
 */
class JsonApi {
    
    public $database;
    private $photosManager;
    private $videoManager;

    function __construct(Nette\Database\Context $database,
                         PhotosManager $photosManager,
                         VideoManager $videoManager) {
        $this->database = $database;
        $this->photosManager = $photosManager;
        $this->videoManager = $videoManager;
    }
    
}
