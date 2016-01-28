<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\StringUtils,
 Model\VideoManager;

/**
 * Description of TagsManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class TagsManager extends BaseModel {
   
    
    /** @var Nette\Database\Context */
    public static $database;
    public static $videoManager;

    public function __construct(Nette\Database\Context $database, VideoManager $videoManager) {
        self::$database = $database;
        self::$videoManager = $videoManager;
    }
    
    public function tagCloud($published = 1) {
        
        $query= self::$database->query("(SELECT tags FROM ".VideoManager::TABLE_NAME.
                " WHERE ".VideoManager::COLUMN_PUBLISHED." = ".$published.
                ") UNION ALL (".
                "SELECT tags FROM ".PhotosManager::TABLE_NAME_ALBUMS.
                " WHERE ".PhotosManager::COLUMN_PUBLISHED. " = ".$published.");")
                ->fetchAll();
        
        $tagArray = '';
        
        foreach($query as $line) {
            $tagArray .= ",".$line['tags'];
        }
        
        $tagArray = array_count_values(explode(',', $tagArray));
        arsort($tagArray);
        
        return $tagArray;
        
    }
    
}
