<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Nette\Application\Responses\JsonResponse,
 Model\ArchiveManager,
 Model\VideoManager,
 Model\PhotosManager,
 Model\CategoriesManager,
 Model\AnalyticsManager,
 Model\SearchManager,
 Model\LiveStreamManager,
 Model\FrontPageManager;

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
    
    public function __construct(Nette\DI\Container $container,
            Nette\Database\Context $database, ArchiveManager $archiveManager, 
            VideoManager $videoManager, PhotosManager $photosManager,
            AnalyticsManager $analyticsManager, CategoriesManager $categoriesManager,
            SearchManager $searchManager, LiveStreamManager $liveStreamManager,
            FrontPageManager $frontPageManager) {
        
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
        
        $routerLang = $this->getParameter('locale');
        $this->setupLanguage($this->container, $routerLang);
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
                
            $videoUrlPrefix = SERVER . "/". VIDEOS_FOLDER . $item[VideoManager::COLUMN_ID] . "/";

            $item[VideoManager::COLUMN_MP3_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_MP3_FILE];
            $item[VideoManager::COLUMN_MP4_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_MP4_FILE];
            $item[VideoManager::COLUMN_WEBM_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_WEBM_FILE];
            $item[VideoManager::COLUMN_THUMB_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_THUMB_FILE];
            $item[VideoManager::COLUMN_MP4_FILE_LOWRES] = $videoUrlPrefix . $item[VideoManager::COLUMN_MP4_FILE_LOWRES];
            $item[VideoManager::COLUMN_SUBTITLES_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_SUBTITLES_FILE];
            
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
