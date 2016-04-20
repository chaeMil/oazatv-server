<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Model\CategoriesManager,
 Model\VideoManager;

/**
 * Description of CategoriesPresenter
 *
 * @author chaemil
 */
class CategoriesPresenter extends BasePresenter{
    
    public $database;
    public $categoriesManager;
    public $videoManager;

    function __construct(Nette\Database\Context $database, 
            CategoriesManager $categoriesManager, VideoManager $videoManager) {
        $this->database = $database;
        $this->categoriesManager = $categoriesManager;
        $this->videoManager = $videoManager;
    }
    
    public function actionDefault() {
        
        $categories = $this->categoriesManager->getCategoriesFromDB();
        
        $categoriesArray = array();
        
        foreach($categories as $category) {
            
            $categoryJson = $category->toArray();
            
            $videosFromCategory = $this->videoManager->getVideosFromDBbyCategory($category['id'], 0, 120);
            $videosArray = array();
            foreach($videosFromCategory as $video) {
                $videoNew = $video->toArray();
                $videoNew['type'] = 'video';
                $videosArray[] = $this->createArchiveItem($videoNew);
            }
            
            $categoryJson['videos'] = $videosArray;
            $categoriesArray[] = $categoryJson;
        }
        
        $this->sendJson($categoriesArray);
        
    }
    
}
