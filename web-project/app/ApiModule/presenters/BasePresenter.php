<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Model\LiveChatManager;
use Nette,
 Nette\Application\Responses\JsonResponse,
 Model\ArchiveManager,
 Model\VideoManager,
 Model\PhotosManager,
 Model\CategoriesManager,
 Model\AnalyticsManager,
 Model\SearchManager,
 Model\LiveStreamManager,
 Model\FrontPageManager,
 Model\SongsManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class BasePresenter extends \Nette\Application\UI\Presenter {
    
    public $container;
    public $database;
    public $lang;
    public $archiveManager;
    public $videoManager;
    public $photosManager;
    public $analyticsManager;
    public $categoriesManager;
    public $searchManager;
    public $liveStreamManager;
    public $frontPageManager;
    public $songsManager;
    public $liveChatManager;
    public $request;

    public function __construct(Nette\DI\Container $container,
            Nette\Database\Context $database, ArchiveManager $archiveManager, 
            VideoManager $videoManager, PhotosManager $photosManager,
            AnalyticsManager $analyticsManager, CategoriesManager $categoriesManager,
            SearchManager $searchManager, LiveStreamManager $liveStreamManager,
            FrontPageManager $frontPageManager, SongsManager $songsManager,
            LiveChatManager $liveChatManager) {
        
        parent::__construct();
        
        $this->database = $database;
        $this->container = $container;
        $this->archiveManager = $archiveManager;
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
        $this->analyticsManager = $analyticsManager;
        $this->categoriesManager = $categoriesManager;
        $this->searchManager = $searchManager;
        $this->liveStreamManager = $liveStreamManager;
        $this->frontPageManager = $frontPageManager;
        $this->songsManager = $songsManager;
        $this->liveChatManager = $liveChatManager;
        
        $routerLang = $this->getParameter('locale');
        $this->setupLanguage($this->container, $routerLang);

        $this->request = $container->getByType('Nette\Http\Request');
    }

    public function enableCORS() {
        header("Access-Control-Allow-Origin: *");
    }
    
    public function setupLanguage($container, $lang = null) {
        if ($lang != null) {
            $this->lang = $lang;
        } else {
            $langs = array('cs', 'en'); // app supported languages
            $httpRequest = $container->getByType('Nette\Http\Request');
            $this->lang = $httpRequest->detectLanguage($langs);
        }
    }
    
    public function createJsonError($errorName, $errorCode, $errorCS, $errorEN) {
        
        $error = array();
        $error['error'] = $errorName;
        $error['error_cs'] = $errorCS;
        $error['error_en'] = $errorEN;
        
        $errorJsonArray = array();
        $errorJsonArray['error'] = $error;
        
        $httpResponse = $this->container->getByType('Nette\Http\Response');
        $httpResponse->setCode($errorCode);
        
        $this->sendResponse(new JsonResponse($errorJsonArray));
        
    }
    
    public function createArchiveItem($item) {
        if ($item['type'] == "video") {
                
            $videoUrlPrefix = VIDEOS_FOLDER . $item[VideoManager::COLUMN_ID] . "/";

            $mp3 = $videoUrlPrefix . $item[VideoManager::COLUMN_MP3_FILE];
            $mp4 = $videoUrlPrefix . $item[VideoManager::COLUMN_MP4_FILE];
            //$webm = $videoUrlPrefix . $item[VideoManager::COLUMN_WEBM_FILE];
            $thumb = $videoUrlPrefix . $item[VideoManager::COLUMN_THUMB_FILE];
            $thumbLowRes = $videoUrlPrefix . "thumbs/" . str_replace(".jpg", "_128.jpg",
                    $item[VideoManager::COLUMN_THUMB_FILE]);
            $mp4LowRes = $videoUrlPrefix . $item[VideoManager::COLUMN_MP4_FILE_LOWRES];
            $subtitles = $videoUrlPrefix . $item[VideoManager::COLUMN_SUBTITLES_FILE];

            $metadata = $this->videoManager->getVideoFileMetadata("",
                $item[VideoManager::COLUMN_METADATA_DURATION_IN_SECONDS]);

            $item[VideoManager::COLUMN_MP3_FILE] = NULL;
            $item[VideoManager::COLUMN_MP4_FILE] = NULL;
            //$item[VideoManager::COLUMN_WEBM_FILE] = NULL;
            $item[VideoManager::COLUMN_THUMB_FILE] = NULL;
            $item[VideoManager::COLUMN_THUMB_FILE_LOWRES] = NULL;
            $item[VideoManager::COLUMN_MP4_FILE_LOWRES] = NULL;
            $item[VideoManager::COLUMN_SUBTITLES_FILE] = NULL;
            $item[VideoManager::API_METADATA] = $metadata;

            if (file_exists($mp3) && is_file($mp3)) {
                $item[VideoManager::COLUMN_MP3_FILE] = SERVER . $mp3;
            }

            if (file_exists($mp4) && is_file($mp4)) {
                $item[VideoManager::COLUMN_MP4_FILE] = SERVER . $mp4;
            }

            /*if (file_exists($webm) && is_file($webm)) {
                $item[VideoManager::COLUMN_WEBM_FILE] = SERVER . $webm;
            }*/

            if (file_exists($thumb) && is_file($thumb)) {
                $item[VideoManager::COLUMN_THUMB_FILE] = SERVER . $thumb;;
            }

            if (file_exists($thumbLowRes) && is_file($thumbLowRes)) {
                $item[VideoManager::COLUMN_THUMB_FILE_LOWRES] = SERVER . $thumbLowRes;;
            }

            if (file_exists($mp4LowRes) && is_file($mp4LowRes)) {
                $item[VideoManager::COLUMN_MP4_FILE_LOWRES] = SERVER . $mp4LowRes;
            }

            if (file_exists($subtitles) && is_file($subtitles)) {
                $item[VideoManager::COLUMN_SUBTITLES_FILE] = SERVER . $subtitles;
            }
        }

        if ($item['type'] == "album") {

            $photoUrlPrefix = SERVER . "/". ALBUMS_FOLDER . $item['id'] . "/";

            $coverPhotoId = $item['cover_photo_id'];

            $coverPhotoThumbs = $this->photosManager->getPhotoThumbnails($coverPhotoId);
            $coverPhotoOriginal = $this->photosManager->getPhotoFromDB($coverPhotoId);

            $thumbs = array();

            $thumbs['original_file'] = $photoUrlPrefix . $coverPhotoOriginal['file'];
            $thumbs['thumb_128'] = SERVER . $coverPhotoThumbs['128'];
            $thumbs['thumb_256'] = SERVER . $coverPhotoThumbs['256'];
            $thumbs['thumb_512'] = SERVER . $coverPhotoThumbs['512'];
            $thumbs['thumb_1024'] = SERVER . $coverPhotoThumbs['1024'];
            $thumbs['thumb_2048'] = SERVER . $coverPhotoThumbs['2048'];

            $item['thumbs'] = $thumbs;
        }

        unset($item['published']);
        //unset($item['id']);
        unset($item['note']);
        unset($item['original_file']);
        
        return $item;
    }
    
    public function sendHTTPResponse($response) {
        $httpResponse = $this->container->getByType('Nette\Http\Response');
        $httpResponse->setCode($response);
        $this->terminate();
    }
}
