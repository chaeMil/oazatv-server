<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\ApiModule;

use Nette,
 Nette\Application\Responses\JsonResponse,
 Nette\Database\Context,
 Model\ArchiveManager,
 Model\VideoManager,
 Model\PhotosManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class ArchivePresenter extends BasePresenter {
    
    private $archiveManager;
    private $videoManager;
    private $photosManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, ArchiveManager $archiveManager, 
            VideoManager $videoManager, PhotosManager $photosManager) {
        
        parent::__construct($container, $database);
        $this->archiveManager = $archiveManager;
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
    }
   
    public function renderDefault($id = 1) {
        $page = $id;
        
        $paginator = new Nette\Utils\Paginator;
        $paginator->setItemCount($this->archiveManager->countArchive());
        $paginator->setItemsPerPage(16);
        $paginator->setPage($page);

        $db = $this->archiveManager
                ->getVideoAndPhotoAlbumsFromDBtoAPI(
                        $paginator->getOffset(), 
                        $paginator->getItemsPerPage());
        
        $archiveArray = array();
        
        foreach($db as $item) {
            
            
            if ($item['type'] == "video") {
                
                $videoUrlPrefix = SERVER . "/". VIDEOS_FOLDER . $item[VideoManager::COLUMN_ID] . "/";
            
                $item[VideoManager::COLUMN_MP3_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_MP3_FILE];
                $item[VideoManager::COLUMN_MP4_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_MP4_FILE];
                $item[VideoManager::COLUMN_WEBM_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_WEBM_FILE];
                $item[VideoManager::COLUMN_THUMB_FILE] = $videoUrlPrefix . $item[VideoManager::COLUMN_THUMB_FILE];
                
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
            
            $archiveArray[] = $item;
 
        }
        
        $jsonArray['archive'] = $archiveArray;
        
        $this->sendJson($jsonArray);
    }
}
