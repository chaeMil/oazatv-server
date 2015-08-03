<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\StringUtils;

/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class VideoManager extends BaseModel {
    
    const
            TABLE_NAME = 'db_video_files',
            COLUMN_ID = 'id',
            COLUMN_ORIGINAL_FILE = 'original_file',
            COLUMN_MP4_FILE = 'mp4_file',
            COLUMN_WEBM_FILE = 'webm_file',
            COLUMN_MP3_FILE = 'mp3_file',
            COLUMN_THUMB_FILE = 'thumb_file',
            COLUMN_DATE = 'date',
            COLUMN_NAME_CS = 'name_cs',
            COLUMN_NAME_EN = 'name_en',
            COLUMN_TAGS = 'tags',
            COLUMN_CATEGORIES = 'categories',
            COLUMN_DESCRIPTION_CS = 'description_cs',
            COLUMN_DESCRIPTION_EN = 'description_en',
            COLUMN_NOTE = 'note';
           
    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        $this::$database = $database;
    }
    
    private function checkIfVideoExists($id) {
        return $this->database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }

    public function saveVideoToDB($values) {
        
        if (isset($values->id) && $this->checkIfVideoExists($values->id) > 0) {
            $sql = $this::$database->table(self::TABLE_NAME)
                    ->where(self::COLUMN_ID, $values->id)
                    ->update($values);
        } else {
            $sql = $this::$database->table(self::TABLE_NAME)->insert($values);
        }
        
        return $sql->id;
       
    }
    
    public function getVideoFromDB($id) {
        return $this::$database->table(self::TABLE_NAME)->get($id);
    }
    
}
