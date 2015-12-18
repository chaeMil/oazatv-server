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
    
    
    public function search($userInput, $lang, $limit = 5, $offset = 0, $jsonApi = false) {
        
        if (strlen($userInput) >= 3) {
            
            $userInput = preg_replace('!\s+!', ' ', $userInput);
            $userInput = str_replace(' ', '%', $userInput);
            $userInputAscii = \Nette\Utils\Strings::toAscii($userInput);
        
            $videoSearch = self::$database->table(VideoManager::TABLE_NAME)
                    ->select('*')                    
                    ->where(VideoManager::COLUMN_PUBLISHED." = 1 AND ((".
                            VideoManager::COLUMN_NAME_CS." LIKE ? OR ".
                            VideoManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                            VideoManager::COLUMN_TAGS." LIKE ? ) OR (".
                            
                            VideoManager::COLUMN_NAME_CS." LIKE ? OR ".
                            VideoManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                            VideoManager::COLUMN_TAGS." LIKE ? ))",
                            
                            "%".$userInput."%", "%".$userInput."%", "%".$userInput ."%",
                            "%".$userInputAscii."%", "%".$userInputAscii."%", "%".$userInputAscii ."%")
                    ->limit($limit, $offset)
                    ->fetchAll();

            $videoSearchOut = array();

            foreach($videoSearch as $video) {
                if ($jsonApi) {
                    
                    $videoOut = $video->toArray();
                    
                    $videoUrlPrefix = SERVER . "/". VIDEOS_FOLDER . $videoOut[VideoManager::COLUMN_ID] . "/";
            
                    $videoOut[VideoManager::COLUMN_MP3_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_MP3_FILE];
                    $videoOut[VideoManager::COLUMN_MP4_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_MP4_FILE];
                    $videoOut[VideoManager::COLUMN_WEBM_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_WEBM_FILE];
                    $videoOut[VideoManager::COLUMN_THUMB_FILE] = $videoUrlPrefix . $video[VideoManager::COLUMN_THUMB_FILE];
                    
                } else {
                    $videoOut = $this->videoManager->createLocalizedVideoObject($lang, $video);
                } 
                
                $videoOut['type'] = 'video';
                $videoSearchOut[] = $videoOut;
            }

            $output['videos'] = $videoSearchOut;

            $albumsSearch = self::$database->table(PhotosManager::TABLE_NAME_ALBUMS)
                    ->select('*')                    
                    ->where(VideoManager::COLUMN_PUBLISHED." = 1 AND ((".
                            VideoManager::COLUMN_NAME_CS." LIKE ? OR ".
                            VideoManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                            VideoManager::COLUMN_TAGS." LIKE ? ) OR (".
                            
                            VideoManager::COLUMN_NAME_CS." LIKE ? OR ".
                            VideoManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                            VideoManager::COLUMN_TAGS." LIKE ? ))",
                            
                            "%".$userInput."%", "%".$userInput."%", "%".$userInput ."%",
                            "%".$userInputAscii."%", "%".$userInputAscii."%", "%".$userInputAscii ."%")
                    ->limit($limit, $offset)
                    ->fetchAll();

            $albumsSearchOut = array();

            foreach($albumsSearch as $album) {
                if ($jsonApi) {
                    
                    $albumOut = $album->toArray();
                
                } else {
                    $albumOut = $this->photosManager
                            ->createLocalizedAlbumThumbObject($lang, $album);
                }
                
                $albumOut['type'] = 'album';
                $albumsSearchOut[] = $albumOut;
            }

            $output['albums'] = $albumsSearchOut;

            return $output;
        }
        
        else {
            return "";
        }
    }
    
}
