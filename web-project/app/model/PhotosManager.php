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
            COLUMN_COVER_PHOTO_ID = 'cover_photo_id',
            COLUMN_DATE = 'date',
            COLUMN_DAYS = 'days',
            COLUMN_TAGS = 'tags',
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

    public function deleteAlbum($id) {
        $album = $this->database->table(self::TABLE_NAME_ALBUMS)
                ->get($id);

        $photos = $this->database->table(self::TABLE_NAME_PHOTOS)
                ->select('*')
                ->where(self::COLUMN_ALBUM_ID, $id);

        FileUtils::recursiveDelete(ALBUMS_FOLDER.$id);

        $photos->delete();
        $album->delete();
    }

    public function getAlbumFromDB($id, $published = 1) {
        if ($published != 2) {
            return $this->database->table(self::TABLE_NAME_ALBUMS)
                    ->select("*")->where(array(self::COLUMN_ID=> $id,
                        self::COLUMN_PUBLISHED => $published))
                    ->fetch();
        } else {
            return $this->database->table(self::TABLE_NAME_ALBUMS)
                    ->select("*")->where(array(self::COLUMN_ID=> $id))
                    ->fetch();
        }

    }

    public function getAlbumFromDBbyHash($hash, $published = 1) {
        if ($published != 2) {
            return $this->database->table(self::TABLE_NAME_ALBUMS)
                    ->select("*")->where(array(self::COLUMN_HASH=> $hash,
                        self::COLUMN_PUBLISHED => $published))
                    ->fetch();
        } else {
            return $this->database->table(self::TABLE_NAME_ALBUMS)
                    ->select("*")->where(array(self::COLUMN_HASH => $hashd))
                    ->fetch();
        }

    }

    public function countAlbums($published = 1) {

        if ($published != 2) {

            return $this->database->table(self::TABLE_NAME_ALBUMS)
                    ->where(self::COLUMN_PUBLISHED, $published)
                    ->count("*");

        } else {

            return $this->database->table(self::TABLE_NAME_ALBUMS)->count("*");

        }


    }

    public function getAlbumsFromDB($from, $count, $published = 1,
        $order = "date DESC, id DESC") {

        if($published != 2) {
            return $this->database->table(self::TABLE_NAME_ALBUMS)
                ->select('*')
                ->where(array(self::COLUMN_PUBLISHED => $published))
                ->limit($count, $from)
                ->order($order);
        } else {
            return $this->database->table(self::TABLE_NAME_ALBUMS)
                ->select('*')
                ->limit($count, $from)
                ->order($order);
        }
    }
    
     public function getAlbumsFromDBtoAPI($from, $count, $order = "date DESC, id DESC") {

        $albums = $this->database->table(self::TABLE_NAME_ALBUMS)
                ->select('id')
                ->limit($count, $from)
                ->order($order);
        
        $outputArray = array();
        
        foreach($albums as $album) {
            $arrayItemFromDB = $this->getAlbumFromDB($album['id'])->toArray();
            $arrayItemFromDB['type'] = 'album';
            $outputArray[] = $arrayItemFromDB;
        }
        
        return $outputArray;
    }

    public function savePhotoToDB($values) {
        $this->database->table(self::TABLE_NAME_PHOTOS)->insert($values);
    }

    public function updatePhotoInDB($values) {
        $photo = $this->database->table(self::TABLE_NAME_PHOTOS)
                ->get($values['id']);
        $photo->update($values);
    }

    public function getPhotoFromDB($id) {
        return $this->database->table(self::TABLE_NAME_PHOTOS)
                ->select("*")->where(self::COLUMN_ID, $id)->fetch();
    }

    public function getPhotoThumbnails($photoId) {
        $photo = $this->getPhotoFromDB($photoId);
        if ($photo) {
            $thumbsLocation = ALBUMS_FOLDER.$photo->album_id.'/thumbs/';
            $thumbLocation = ALBUMS_FOLDER.$photo->album_id.'/thumbs/'.str_replace(".jpg", "_".self::THUMB_1024.".jpg", $photo->file);
            if (!file_exists($thumbsLocation)) {
                if (file_exists(ALBUMS_FOLDER.$photo->album_id)) {
                    mkdir($thumbsLocation);
                    chmod($thumbsLocation, 0777);
                }
            }      
            if (file_exists($thumbsLocation)) {
                $thumb = $thumbLocation;
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
        }
    }

    public function generatePhotoThumbnails($photoId) {
        $photo = $this->database->table(self::TABLE_NAME_PHOTOS)
                ->select('*')
                ->where(self::COLUMN_ID, $photoId)
                ->fetch();

        if (file_exists(ALBUMS_FOLDER.$photo->album_id.'/'.$photo->file)) {

            $thumbsDir = ALBUMS_FOLDER.$photo->album_id.'/thumbs/';
            if (!file_exists($thumbsDir)) {
               mkdir($thumbsDir); 
            }
            
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
                ->order(self::COLUMN_ORDER, "ASC")
                ->where(self::COLUMN_ALBUM_ID, $aldumId)->fetchAll();
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

    public function createLocalizedAlbumThumbObject($lang, $input) {
        $album = Array();

        switch($lang) {
            case 'cs':
                $day = date('d', strtotime($input[self::COLUMN_DATE]));
                $month = date('n', strtotime($input[self::COLUMN_DATE]));
                $year = date('Y', strtotime($input[self::COLUMN_DATE]));

                $album['name'] = $input[self::COLUMN_NAME_CS];
                $album['date'] = StringUtils::formatCzechDate($year, $month, $day);
                $album['desc'] = $input[self::COLUMN_DESCRIPTION_CS];
                break;
            case 'en':
                $day = date('d', strtotime($input[self::COLUMN_DATE]));
                $month = date('n', strtotime($input[self::COLUMN_DATE]));
                $year = date('Y', strtotime($input[self::COLUMN_DATE]));

                $album['name'] = $input[self::COLUMN_NAME_EN];
                $album['date'] = StringUtils::formatEnglishDate($year, $month, $day);
                $album['desc'] = $input[self::COLUMN_DESCRIPTION_EN];
                break;
        }


        $tags = explode(",", $input[self::COLUMN_TAGS]);
        
        $album['id'] = $input[self::COLUMN_ID];
        $album['hash'] = $input[self::COLUMN_HASH];
        $album['tags'] = $tags;
        $album['days'] = $input[self::COLUMN_DAYS];

        if ($input[self::COLUMN_COVER_PHOTO_ID] != '') {
            $album['thumbs'] = $this->getPhotoThumbnails($input[self::COLUMN_COVER_PHOTO_ID]);
        } else {
            $album['thumbs'] = null;
        }
        $album['type'] = "album";


        return $album;
    }

    public function createLocalizedAlbumPhotosObject($lang, $albumId) {
        $photos = Array();

        $input = $this->getPhotosFromAlbum($albumId);

        foreach($input as $photo) {

            $photoOut['id'] = $photo[self::COLUMN_ID];
            $photoOut['thumbs'] = $this->getPhotoThumbnails($photo[self::COLUMN_ID]);
            $photoOut['order'] = $photo[self::COLUMN_ORDER];
            $photoOut['dimensions'] = ImageUtils::getImageDimensions(
                    $this->getPhotoThumbnails($photo[self::COLUMN_ID])[self::THUMB_2048]);

            switch($lang) {
                case 'cs':
                    $photoOut['desc'] = $photo[self::COLUMN_DESCRIPTION_CS];
                    break 1;
                case 'en':
                    $photoOut['desc'] = $photo[self::COLUMN_DESCRIPTION_EN];
                    break 1;
            }

            $photos[] = $photoOut;
        }

        return $photos;
    }
}
