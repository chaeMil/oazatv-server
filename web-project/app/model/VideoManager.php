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
    
    public function addVideoToDB($original_file, $mp4_file, $webm_file, $mp3_file,
            $thumb_file, $year, $month, $day, $name_cs, $name_en, $tags, 
            $categories, $description_cs, $description_en, $note) {
                
        $sql_date = date('Y-m-d', strtotime($year."-".StringUtils::addLeadingZero($month, 2)."-".StringUtils::addLeadingZero($day, 2)));
        
        $values = Array(self::COLUMN_ORIGINAL_FILE => $original_file,
            self::COLUMN_MP4_FILE => $mp4_file, 
            self::COLUMN_WEBM_FILE => $webm_file,
            self::COLUMN_MP3_FILE => $mp3_file,
            self::COLUMN_THUMB_FILE => $thumb_file,
            self::COLUMN_DATE => $sql_date,
            self::COLUMN_NAME_CS => $name_cs,
            self::COLUMN_NAME_EN => $name_en,
            self::COLUMN_TAGS => $tags,
            self::COLUMN_CATEGORIES => $categories,
            self::COLUMN_DESCRIPTION_CS => $description_cs,
            self::COLUMN_DESCRIPTION_EN => $description_en,
            self::COLUMN_NOTE => $note);
        
        $insert = $this::$database->table(self::TABLE_NAME)->insert($values);
        
        return $insert->id;
       
    }
    
    public function getVideoFromDB($id) {
        return $this::$database->table(self::TABLE_NAME)->get($id);
    }
    
}
