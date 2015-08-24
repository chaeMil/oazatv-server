<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\FileUtils,
 App\StringUtils,
 App\ImageUtils;

/**
 * Description of PhotosManager
 *
 * @author chaemil
 */
class PhotosManager {
    
    const
            TABLE_NAME_PHOTOS = 'db_photo_files',
            COLUMN_ID = 'id',
            COLUMN_ALBUM_ID = 'album_id',
            COLUMN_FILE = 'file',
            COLUMN_ORDER = 'order',
            COLUMN_DESCRIPTION_CS = 'description_cs',
            COLUMN_DESCRIPTION_EN = 'description_en',
            TABLE_NAME_ALBUMS = 'db_albums',
            COLUMN_HASH = 'hash',
            COLUMN_PUBLISHED = 'published',
            COLUMN_NAME_EN = 'name_en',
            COLUMN_NAME_CS = 'name_cs',
            COLUMN_DATE = 'date',
            COLUMN_DAYS = 'days',
            THUMB_2048 = 2048,
            THUMB_1024 = 1024,
            THUMB_512 = 512,
            THUMB_256 = 256,
            THUMB_128 = 128;
            
    
    /** @var Nette\Database\Context */
    public $database;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }
    
    public function saveAlbumToDB($values) {
        if(isset($values['id'])) {
            $albumId = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $albumId = 0;
        }
        
        if ($albumId) {
            $album = $this->database->table(self::TABLE_NAME_ALBUMS)->get($albumId);
            $sql = $album->update($values);
            return $albumId;
        } else {
            $values['hash'] = StringUtils::rand(8);
            $sql = $this->database->table(self::TABLE_NAME_ALBUMS)->insert($values);
            $newAlbumDir = ALBUMS_FOLDER.$sql->id."/";
            $newAlbumThumbsDir = $newAlbumDir."thumbs/";
            mkdir($newAlbumDir);
            mkdir($newAlbumThumbsDir);
            chmod($newAlbumDir, 0777);
            chmod($newAlbumThumbsDir, 0777);
        }
        
        return $sql->id;
    }
    
    public function getAlbumFromDB($id) {
        return $this->database->table(self::TABLE_NAME_ALBUMS)
                ->select("*")->where(self::COLUMN_ID, $id)->fetch();
    }
    
    public function countAlbums() {
        return $this->database->table(self::TABLE_NAME_ALBUMS)->count("*");
    }
    
    public function getAlbumsFromDB($from, $count, $order) {
        return $this->database->table(self::TABLE_NAME_ALBUMS)
                ->limit($from, $count)
                ->order($order);
    }
    
    public function savePhotoToDB($values) {
        $this->database->table(self::TABLE_NAME_PHOTOS)->insert($values);
    }
    
    public function getPhotoFromDB($id) {
        return $this->database->table(self::TABLE_NAME_PHOTOS)
                ->select("*")->where(self::COLUMN_ID, $id)->fetch();
    }
    
    public function getPhotoThumbnails($photoId) {
        $photo = $this->getPhotoFromDB($photoId);
        $thumb = ALBUMS_FOLDER.$photo->album_id.'/thumbs/'.str_replace(".jpg", "_".self::THUMB_1024.".jpg", $photo->file);
        $thumbfile = ALBUMS_FOLDER.$photo->album_id.'/thumbs/'.str_replace(".jpg", "", $photo->file);
        if (!file_exists($thumb)) {
            $this->generatePhotoThumbnails($photoId);
        }
        return array(self::THUMB_2048 => $thumbfile."_".self::THUMB_2048.".jpg",
                self::THUMB_1024 => $thumbfile."_".self::THUMB_1024.".jpg",
                self::THUMB_512 => $thumbfile."_".self::THUMB_512.".jpg",
                self::THUMB_256 => $thumbfile."_".self::THUMB_256.".jpg",
                self::THUMB_128 => $thumbfile."_".self::THUMB_128.".jpg");
    }
    
    public function generatePhotoThumbnails($photoId) {
        $photo = $this->database->table(self::TABLE_NAME_PHOTOS)
                ->select('*')
                ->where(self::COLUMN_ID, $photoId)
                ->fetch();
        
        if (file_exists(ALBUMS_FOLDER.$photo->album_id.'/'.$photo->file)) {
        
            ImageUtils::resizeImage(ALBUMS_FOLDER.$photo->album_id.'/', $photo->file, 
                    self::THUMB_2048, self::THUMB_2048, ALBUMS_FOLDER.$photo->album_id.'/thumbs/');
            ImageUtils::resizeImage(ALBUMS_FOLDER.$photo->album_id.'/', $photo->file, 
                    self::THUMB_1024, self::THUMB_1024, ALBUMS_FOLDER.$photo->album_id.'/thumbs/');
            ImageUtils::resizeImage(ALBUMS_FOLDER.$photo->album_id.'/', $photo->file, 
                    self::THUMB_512, self::THUMB_512, ALBUMS_FOLDER.$photo->album_id.'/thumbs/');
            ImageUtils::resizeImage(ALBUMS_FOLDER.$photo->album_id.'/', $photo->file, 
                    self::THUMB_256, self::THUMB_256, ALBUMS_FOLDER.$photo->album_id.'/thumbs/');
            ImageUtils::resizeImage(ALBUMS_FOLDER.$photo->album_id.'/', $photo->file, 
                    self::THUMB_128, self::THUMB_128, ALBUMS_FOLDER.$photo->album_id.'/thumbs/');
        }
    }
    
    public function deletePhotoThumbnails($photoId) {
        foreach($this->getPhotoThumbnails($photoId) as $thumbnail) {
            if (file_exists($thumbnail)) {
                unlink($thumbnail);
            }
        } 
    }
    
    public function deletePhoto($photoId) {
        $photo = $this->database->table(self::TABLE_NAME_PHOTOS)
                ->select('*')
                ->where(self::COLUMN_ID, $photoId)
                ->fetch();
        
        $this->deletePhotoThumbnails($photoId);
        $file = ALBUMS_FOLDER.$photo->album_id."/".$photo->file;
        if (file_exists($file)) {
            unlink($file);
        }
        
        $photo->delete();        
    }
    
    public function getPhotosFromAlbum($aldumId) {
        return $this->database->table(self::TABLE_NAME_PHOTOS)
                ->select('*')
                ->where(self::COLUMN_ALBUM_ID, $aldumId);
    }
    
    public function countPhotosInAlbum($albumId) {
        return $this->database->table(self::TABLE_NAME_PHOTOS)
                ->count('*')
                ->where(self::COLUMN_ALBUM_ID, $albumId);
    }
    
    public function getAlbumMaxOrderNumber($albumId) {
        $photos = $this->database->table(self::TABLE_NAME_PHOTOS)
                ->select(self::COLUMN_ORDER)
                ->where(self::COLUMN_ALBUM_ID, $albumId);
        
        $maxOrderNum = 0;
        foreach($photos as $photo) {
            if ($photo->order > $maxOrderNum) {
                $maxOrderNum = $photo->order;
            }
        }
        
        return $maxOrderNum + 1;
    }
}
