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
    private $videoManager;
    private $photosManager;

    public function __construct(Nette\Database\Context $database,
            VideoManager $videoManager, PhotosManager $photosManager) {
        self::$database = $database;
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
    }
    
    
    public function getVideosAndPhotoAlbumsFromDB($from, $count, $lang, 
            $published = 1, $order = "date DESC") {
        
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

            $dbOutput = self::$database->query($query)->fetchAll();
            
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
            
            $dbOutput = self::$database->query($query)->fetchAll();
        }
        
        $outputArray = array();
            
        foreach($dbOutput as $output) {
            
            switch($output['type']) {
                case 'video':

                    $rawItemFromDB = $this->videoManager
                        ->getVideoFromDB($output['id']);

                    $video = $this->videoManager
                            ->createLocalizedVideoObject($lang,
                                    $rawItemFromDB);
                    
                    $video['type'] = 'video';

                    $outputArray[] = $video;
                    break;

                case 'album':

                    $rawItemFromDB = $this->photosManager
                        ->getAlbumFromDB($output['id']);

                    $album = $this->photosManager
                            ->createLocalizedAlbumThumbObject($lang,
                                    $rawItemFromDB);
                    
                    $album['type'] = 'album';

                    $outputArray[] = $album;
                    break;
            }
        
        }
        
        return $outputArray;
    } 
    
    
    public function countArchive($published = 1) {
        if($published != 2) {
            
            $videoCount = $this->videoManager->countVideos();
            $albumCount = $this->photosManager->countAlbums();
            
            
        } else {
            
            $videoCount = $this->videoManager->countVideos(2);
            $albumCount = $this->photosManager->countAlbums(2);
            
        }
        
        return $videoCount + $albumCount;
    }
   
}