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

    const
        TABLE_NAME = 'search_logs',
        COLUMN_ID = 'id',
        COLUMN_INPUT = 'input';
    
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


    public function search($userInput, $offset = 0, $limit = 5, $lang = 'cs') {
        
        $userInput = trim(urldecode($userInput));
        
        //log user inpupt
        self::$database->table(self::TABLE_NAME)
                ->insert(array(self::COLUMN_INPUT => $userInput));
        
        $hashTag = false;
        
        if (strlen($userInput) >= 3 && strlen($userInput) <= 100) {
            
            if (substr($userInput, 0, 1) === "#") {
                $hashTag = true;
            }

            $userInput = preg_replace('!\s+!', ' ', $userInput);
            $userInput = str_replace('%20', ' ', $userInput);
            $userInput = str_replace(array(",","."), "", $userInput); // remove invalid characters
            //$userInput = str_replace(' ', '%', $userInput);
            $userInputAscii = \Nette\Utils\Strings::toAscii($userInput);
            
            if ($hashTag) {
                
                $query = "SELECT * FROM ".VideoManager::TABLE_NAME." WHERE ".VideoManager::COLUMN_PUBLISHED." = 1 AND ";
                $tagsArray = explode(' ', $userInput);
                
                $i = 0;
                $len = count($tagsArray);
                foreach($tagsArray as $tag) {
                    $query .= " ".VideoManager::COLUMN_TAGS." LIKE '%".str_replace('#', '', $tag)."%' ";
                    
                    if ($i != $len - 1) {
                        $query .= " AND ";
                    }
                    $i++;
                }
                
                $query .= " ORDER BY ".VideoManager::COLUMN_DATE." DESC LIMIT ".$limit." OFFSET ".$offset;
                
                $videoSearch = self::$database->query($query)->fetchAll();
                
            } else {
                
                $inputArray = explode(' ', $userInput);
                
                $query = "SELECT * FROM ".VideoManager::TABLE_NAME." WHERE ".VideoManager::COLUMN_PUBLISHED." = 1 AND ";

                $i = 0;
                $len = count($inputArray);
                foreach($inputArray as $input) {
                    
                    $inputAscii = \Nette\Utils\Strings::toAscii($input);
                    
                    $query .= " ((".VideoManager::COLUMN_NAME_CS." LIKE '%".$input."%' OR ".
                            VideoManager::COLUMN_DESCRIPTION_EN." LIKE '%".$input."%' ) OR (".
                            VideoManager::COLUMN_TAGS." LIKE '%".$input."%' ) OR ( "
                            
                            .VideoManager::COLUMN_NAME_CS." LIKE '%".$inputAscii."%' OR ".
                            VideoManager::COLUMN_DESCRIPTION_EN." LIKE '%".$inputAscii."%' ) OR (".
                            VideoManager::COLUMN_TAGS." LIKE '%".$inputAscii."%' )) ";;
                    
                    if ($i != $len - 1) {
                        $query .= " AND ";
                    }
                    $i++;
                }
                
                $query .= " ORDER BY ".VideoManager::COLUMN_DATE." DESC LIMIT ".$limit." OFFSET ".$offset;
                
                $videoSearch = self::$database->query($query)->fetchAll();
               
            }

            $videoSearchOut = array();

            foreach($videoSearch as $video) {
                if ($video instanceof \Nette\Database\Table\ActiveRow) {
                    $videoOut = $video->toArray();
                } else {
                    $videoOut = $video;
                }

                $videoPrefix = VIDEOS_FOLDER . $video[VideoManager::COLUMN_ID] . "/";

                $mp3 = $videoPrefix . $video[VideoManager::COLUMN_MP3_FILE];
                $mp4 = $videoPrefix . $video[VideoManager::COLUMN_MP4_FILE];
                $webm = $videoPrefix . $video[VideoManager::COLUMN_WEBM_FILE];
                $thumb = $videoPrefix . $video[VideoManager::COLUMN_THUMB_FILE];
                $mp4LowRes = $videoPrefix . $video[VideoManager::COLUMN_MP4_FILE_LOWRES];
                $subtitles = $videoPrefix . $video[VideoManager::COLUMN_SUBTITLES_FILE];

                $videoOut[VideoManager::COLUMN_MP3_FILE] = NULL;
                $videoOut[VideoManager::COLUMN_MP4_FILE] = NULL;
                $videoOut[VideoManager::COLUMN_WEBM_FILE] = NULL;
                $videoOut[VideoManager::COLUMN_THUMB_FILE] = NULL;
                $videoOut[VideoManager::COLUMN_MP4_FILE_LOWRES] = NULL;
                $videoOut[VideoManager::COLUMN_SUBTITLES_FILE] = NULL;

                if (file_exists($mp3) && is_file($mp3)) {
                    $videoOut[VideoManager::COLUMN_MP3_FILE] = SERVER . $mp3;
                }

                if (file_exists($mp4) && is_file($mp4)) {
                    $videoOut[VideoManager::COLUMN_MP4_FILE] = SERVER . $mp4;
                }

                if (file_exists($webm) && is_file($webm)) {
                    $videoOut[VideoManager::COLUMN_WEBM_FILE] = SERVER . $webm;
                }

                if (file_exists($thumb) && is_file($thumb)) {
                    $videoOut[VideoManager::COLUMN_THUMB_FILE] = SERVER . $thumb;;
                }

                if (file_exists($mp4LowRes) && is_file($mp4LowRes)) {
                    $videoOut[VideoManager::COLUMN_MP4_FILE_LOWRES] = SERVER . $mp4LowRes;
                }

                if (file_exists($subtitles) && is_file($subtitles)) {
                    $videoOut[VideoManager::COLUMN_SUBTITLES_FILE] = SERVER . $subtitles;
                }

                $videoOut['type'] = 'video';
                switch($lang) {
                    case 'cs':
                        $videoOut['name'] = $video['name_cs'];
                        break;
                    case 'en':
                        $videoOut['name'] = $video['name_en'];
                        break;
                    default:
                        $videoOut['name'] = $video['name_en'];
                        break;
                }
                $videoSearchOut[] = $videoOut;
            }

            $output['videos'] = $videoSearchOut;

            if ($hashTag) {
                $albumsSearch = self::$database->table(PhotosManager::TABLE_NAME_ALBUMS)
                    ->select('*')
                    ->where(PhotosManager::COLUMN_PUBLISHED." = 1 AND (".
                            PhotosManager::COLUMN_TAGS." LIKE ? OR ".

                            PhotosManager::COLUMN_TAGS." LIKE ? )",

                            "%".substr($userInput, 1)."%",
                            "%".substr($userInputAscii, 1)."%")
                    ->limit($limit, $offset)
                    ->order(PhotosManager::COLUMN_DATE. " DESC")
                    ->fetchAll();
            } else {
                $albumsSearch = self::$database->table(PhotosManager::TABLE_NAME_ALBUMS)
                        ->select('*')
                        ->where(PhotosManager::COLUMN_PUBLISHED." = 1 AND ((".
                                PhotosManager::COLUMN_NAME_CS." LIKE ? OR ".
                                PhotosManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                                PhotosManager::COLUMN_TAGS." LIKE ? ) OR (".

                                PhotosManager::COLUMN_NAME_CS." LIKE ? OR ".
                                PhotosManager::COLUMN_NAME_EN." LIKE ? ) OR (".
                                PhotosManager::COLUMN_TAGS." LIKE ? ))",

                                "%".$userInput."%", "%".$userInput."%", "%".$userInput ."%",
                                "%".$userInputAscii."%", "%".$userInputAscii."%", "%".$userInputAscii ."%")
                        ->limit($limit, $offset)
                        ->order(PhotosManager::COLUMN_DATE. " DESC")
                        ->fetchAll();
            }

            $albumsSearchOut = array();

            foreach($albumsSearch as $album) {

                $albumOut = $album->toArray();

                $photoUrlPrefix = SERVER . "/". ALBUMS_FOLDER . $albumOut['id'] . "/";

                $coverPhotoId = $albumOut['cover_photo_id'];
                $coverPhotoThumbs = $this->photosManager->getPhotoThumbnails($coverPhotoId);
                $coverPhotoOriginal = $this->photosManager->getPhotoFromDB($coverPhotoId);

                $thumbs = array();

                $thumbs['original_file'] = $photoUrlPrefix . $coverPhotoOriginal['file'];
                $thumbs['thumb_128'] = SERVER . $coverPhotoThumbs['128'];
                $thumbs['thumb_256'] = SERVER . $coverPhotoThumbs['256'];
                $thumbs['thumb_512'] = SERVER . $coverPhotoThumbs['512'];
                $thumbs['thumb_1024'] = SERVER . $coverPhotoThumbs['1024'];
                $thumbs['thumb_2048'] = SERVER . $coverPhotoThumbs['2048'];

                $albumOut['thumbs'] = $thumbs;

                $albumOut['type'] = 'album';
                switch($lang) {
                    case 'cs':
                        $albumOut['name'] = $album['name_cs'];
                        break;
                    case 'en':
                        $albumOut['name'] = $album['name_en'];
                        break;
                    default:
                        $albumOut['name'] = $album['name_en'];
                        break;
                }
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
