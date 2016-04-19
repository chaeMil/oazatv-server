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
            
            $categoryJson['category'] = $category->toArray();
            $categoryJson['videos'] = array();
            
            $videosFromCategory = $this->videoManager->getVideosFromDBbyCategory($category['id'], 0, 120);
            
            foreach($videosFromCategory as $video) {
                $categoryJson['videos'][] = $this->videoManager->getVideoFromDBtoAPI($video['id']);
            }
            
            $categoriesArray[] = $categoryJson;
        }
        
        $this->sendJson($categoriesArray);
        
    }
    
}
