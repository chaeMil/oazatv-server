<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\FileUtils,
 App\StringUtils;

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
            COLUMN_DESCRIPTION_CS = 'description_cs',
            COLUMN_DESCRIPTION_EN = 'description_en',
            TABLE_NAME_ALBUMS = 'db_albums',
            COLUMN_HASH = 'hash',
            COLUMN_PUBLISHED = 'published',
            COLUMN_NAME_EN = 'name_en',
            COLUMN_NAME_CS = 'name_cs',
            COLUMN_DATE = 'date',
            COLUMN_DAYS = 'days';
            
    
    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        $this::$database = $database;
    }
    
    public function saveAlbumToDB($values) {
        if(isset($values['id'])) {
            $albumId = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $albumId = 0;
        }
        
        if ($albumId) {
            $album = $this::$database->table(self::TABLE_NAME_ALBUMS)->get($albumId);
            $sql = $album->update($values);
            return $albumId;
        } else {
            $values['hash'] = StringUtils::rand(8);
            $sql = $this::$database->table(self::TABLE_NAME_ALBUMS)->insert($values);
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
        return $this::$database->table(self::TABLE_NAME_ALBUMS)
                ->select("*")->where(self::COLUMN_ID, $id)->fetch();
    }
    
    public function countAlbums() {
        return $this::$database->table(self::TABLE_NAME_ALBUMS)->count("*");
    }
    
    public function getAlbumsFromDB($from, $count, $order) {
        return $this::$database->table(self::TABLE_NAME_ALBUMS)
                ->limit($from, $count)
                ->order($order);
    }
    
    public function savePhotoToDB($values) {
        $this::$database->table(self::TABLE_NAME_PHOTOS)->insert($values);
    }
}
