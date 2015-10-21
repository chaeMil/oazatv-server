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
    private $videoManager;
    private $photosManager;

    public function __construct(Nette\Database\Context $database,
            VideoManager $videoManager, PhotosManager $photosManager) {
        self::$database = $database;
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
    }
    
    
    public function search($userInput, $lang) {
        
        if (strlen($userInput) >= 3) {
            
            $userInput = preg_replace('!\s+!', ' ', $userInput);
            $userInput = str_replace(' ', '%', $userInput);
            dump($userInput);
        
            $videoSearch = self::$database->table(VideoManager::TABLE_NAME)
                    ->select('*')
                    ->where(VideoManager::COLUMN_PUBLISHED, 1)
                    ->where("(".VideoManager::COLUMN_NAME_CS." LIKE ? OR ".
                            VideoManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                            VideoManager::COLUMN_TAGS." LIKE ? )", 
                            "%".$userInput."%", "%".$userInput."%", "%".$userInput ."%")
                    ->limit(10)
                    ->fetchAll();

            $videoSearchOut = array();

            foreach($videoSearch as $video) {
                $videoSearchOut[] = $this->videoManager
                        ->createLocalizedVideoObject($lang, $video);
            }

            $output['videos'] = $videoSearchOut;

            $albumsSearch = self::$database->table(PhotosManager::TABLE_NAME_ALBUMS)
                    ->select('*')
                    ->where(PhotosManager::COLUMN_PUBLISHED, 1)
                    ->where("(".PhotosManager::COLUMN_NAME_CS." LIKE ? OR ".
                            PhotosManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                            PhotosManager::COLUMN_TAGS." LIKE ? )", 
                            "%".$userInput."%", "%".$userInput."%", "%".$userInput ."%")
                    ->limit(10)
                    ->fetchAll();

            $albumsSearchOut = array();

            foreach($albumsSearch as $album) {
                $albumsSearchOut[] = $this->photosManager
                        ->createLocalizedAlbumThumbObject($lang, $album);
            }

            $output['albums'] = $albumsSearchOut;

            return $output;
        }
        
        else {
            return "";
        }
    }
    
}
