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

    const PER_PAGE = 10;
    public $database;
    public $categoriesManager;
    public $videoManager;

    function __construct(Nette\Database\Context $database, 
            CategoriesManager $categoriesManager, VideoManager $videoManager) {
        $this->database = $database;
        $this->categoriesManager = $categoriesManager;
        $this->videoManager = $videoManager;
    }
    
    public function actionDefault($videos = true, $categoryId, $page = 0, $perPage = self::PER_PAGE) {

        if (!isset($categoryId)) {
            $categories = $this->categoriesManager->getCategoriesFromDB();

            $response = array();
            $categoriesArray = array();

            foreach ($categories as $category) {

                $categoryJson = $category->toArray();

                if ($videos) {
                    $videosFromCategory = $this->videoManager->getVideosFromDBbyCategory($category['id'], 0, $perPage);
                    $videosArray = array();
                    foreach ($videosFromCategory as $video) {
                        $videoNew = $video->toArray();
                        $videoNew['type'] = 'video';
                        $videosArray[] = $this->createArchiveItem($videoNew);
                    }

                    $categoryJson['videos'] = $videosArray;
                }
                $categoriesArray[] = $categoryJson;
            }

            $response['categories'] = $categoriesArray;

            $this->sendJson($response);

        } else {

            $category = $this->categoriesManager->getCategoryFromDB($categoryId);
            $categoryJson = $category->toArray();

            $videosFromCategory = $this->videoManager->getVideosFromDBbyCategory($category['id'], $page * $perPage, $perPage);
            foreach ($videosFromCategory as $video) {
                $videoNew = $video->toArray();
                $videoNew['type'] = 'video';
                $videosArray[] = $this->createArchiveItem($videoNew);
            }

            $categoryJson['videos'] = $videosArray;
            $response['categories'] = $categoryJson;

            $this->sendJson($response);

        }
        
    }
    
}
