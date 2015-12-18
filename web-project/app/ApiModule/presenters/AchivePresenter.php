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
 Model\VideoManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class ArchivePresenter extends BasePresenter {
    
    private $archiveManager;
    private $videoManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, ArchiveManager $archiveManager, 
            VideoManager $videoManager) {
        
        parent::__construct($container, $database);
        $this->archiveManager = $archiveManager;
        $this->videoManager = $videoManager;
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
            
            $archiveArray[] = $item;
 
        }
        
        $jsonArray['archive'] = $archiveArray;
        
        $this->sendJson($jsonArray);
    }
}
