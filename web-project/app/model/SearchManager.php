<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\StringUtils,
 Model\VideoManager,
 Model\PhotosManager;

/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class SearchManager extends BaseModel {
    
    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
    }
    
    
    public function search($userInput) {
        
        $videoSearch = self::$database->table(VideoManager::TABLE_NAME)
                ->select('*')
                ->where(VideoManager::COLUMN_PUBLISHED, 1)
                ->where("(".VideoManager::COLUMN_NAME_CS." LIKE ? OR ".
                        VideoManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                        VideoManager::COLUMN_TAGS." LIKE ? )", 
                        $userInput . "%", $userInput . "%", $userInput ."%")
                ->limit(10)
                ->fetchAll();
        
        
        $output['videoSearch'] = $videoSearch;
        
        $albumsSearch = self::$database->table(PhotosManager::TABLE_NAME_ALBUMS)
                ->select('*')
                ->where(PhotosManager::COLUMN_PUBLISHED, 1)
                ->where("(".PhotosManager::COLUMN_NAME_CS." LIKE ? OR ".
                        PhotosManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                        PhotosManager::COLUMN_TAGS." LIKE ? )", 
                        $userInput . "%", $userInput . "%", $userInput ."%")
                ->limit(10)
                ->fetchAll();

        $output['albumsSearch'] = $albumsSearch;
        
        return $output;
    }
    
}
