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
 Model\PhotosManager;

/**
 * Description of MainPresenter
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class AlbumPresenter extends BasePresenter {
    
    private $photosManager;
    
    public function __construct(Nette\DI\Container $container,
            Context $database, PhotosManager $photosManager) {
        
        parent::__construct($container, $database);
        $this->photosManager = $photosManager;
    }
    
    public function actionDefault($id) {
        $hash = $id;
        
        $album = $this->photosManager->getAlbumFromDBbyHash($hash);
        
        if ($album != false) {

            $albumArray = $album->toArray();
            $albumPhotos = $this->photosManager->getPhotosFromAlbum($album['id']);
            
            foreach($albumPhotos as $photo) {
                $photoArray = $photo->toArray();
                
                $photoUrlPrefix = SERVER . "/". ALBUMS_FOLDER . $photoArray[PhotosManager::COLUMN_ALBUM_ID] . "/";
                
                $photoArray['original_file'] = $photoUrlPrefix . $photo['file'];
                $nameHash = str_replace('.jpg', "", $photo['file']);
                $photoArray['thumb_128'] = $photoUrlPrefix . "thumbs/" . $nameHash . "_". PhotosManager::THUMB_128 . ".jpg";
                $photoArray['thumb_256'] = $photoUrlPrefix . "thumbs/" . $nameHash . "_". PhotosManager::THUMB_256 . ".jpg";
                $photoArray['thumb_512'] = $photoUrlPrefix . "thumbs/" . $nameHash . "_". PhotosManager::THUMB_512 . ".jpg";
                $photoArray['thumb_1024'] = $photoUrlPrefix . "thumbs/" . $nameHash . "_". PhotosManager::THUMB_1024 . ".jpg";
                $photoArray['thumb_2048'] = $photoUrlPrefix . "thumbs/" . $nameHash . "_". PhotosManager::THUMB_2048 . ".jpg";
                
                unset($photoArray['id']);
                unset($photoArray['album_id']);
                unset($photoArray['file']);
                
                $albumArray['photos'][] = $photoArray;
            }
            
            dump($albumArray); exit;
            
            $jsonArray['album'] = $albumArray;
            
            $this->sendJson($jsonArray);
        } else {
            
            $this->createJsonError('albumFileNotFound', 
                    Nette\Http\Response::S404_NOT_FOUND, 
                    "Album neexistuje", 
                    "This album does not exist");
            
        }
        
        
    }
}