<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\StringUtils,
 App\FileUtils,
 Model\VideoManager,
 Model\PhotosManager;

class ArchiveManager extends BaseModel {
    
    
    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
    }
    
    
    public function getVideosAndPhotoAlbumsFromDB($from, $count, $published = 1, 
            $order = VideoManager::COLUMN_DATE." DESC") {
        
        if($published != 2) {
            $query = '(SELECT '.VideoManager::COLUMN_ID.
                ", ".VideoManager::COLUMN_DATE.
                ", ".VideoManager::COLUMN_PUBLISHED.
                ", 'video' AS type  FROM ".
                VideoManager::TABLE_NAME.
                " WHERE ".VideoManager::COLUMN_PUBLISHED." = ".$published.
                ") UNION ALL (SELECT ".
                PhotosManager::COLUMN_ID.
                ", ".PhotosManager::COLUMN_DATE.
                ", ".PhotosManager::COLUMN_PUBLISHED.
                ", 'album' AS type FROM ".
                PhotosManager::TABLE_NAME_ALBUMS.
                " WHERE ".PhotosManager::COLUMN_PUBLISHED." = ".$published.
                ") ORDER BY ".$order.
                " LIMIT ".$count.
                " OFFSET ".$from;

            return self::$database->query($query)->fetchAll();
        } else {
            
            $query = '(SELECT '.VideoManager::COLUMN_ID.
                ", ".VideoManager::COLUMN_DATE.
                ", 'video' AS type  FROM ".
                VideoManager::TABLE_NAME.
                ") UNION ALL (SELECT ".
                PhotosManager::COLUMN_ID.
                ", ".PhotosManager::COLUMN_DATE.
                ", 'album' AS type FROM ".
                PhotosManager::TABLE_NAME_ALBUMS.
                ") ORDER BY ".$order.
                " LIMIT ".$count.
                " OFFSET ".$from;
            
            return self::$database->query($query)->fetchAll();
        }
        
    }
    
}