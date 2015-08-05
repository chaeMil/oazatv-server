<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\StringUtils,
 App\FileUtils;

/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class VideoManager extends BaseModel {
    
    const
            TABLE_NAME = 'db_video_files',
            COLUMN_ID = 'id',
            COLUMN_HASH = 'hash',
            COLUMN_PUBLISHED = 'published',
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
        return $this::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }

    public function saveVideoToDB($values) {
        
        if(isset($values['id'])) {
            $videoId = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $videoId = 0;
        }
        
        if ($videoId != 0 && $this->checkIfVideoExists($videoId) > 0) {
            $video = $this::$database->table(self::TABLE_NAME)->get($videoId);
            $sql = $video->update($values);
            return $sql;
        } else {
            $values['hash'] = StringUtils::rand(8);
            $sql = $this::$database->table(self::TABLE_NAME)->insert($values);
            $newVideoDir = VIDEOS_FOLDER.$sql->id."/";
            $newVideoThumbsDir = $newVideoDir."thumbs/";
            $vewVideoLogsDir = $newVideoDir."logs/";
            mkdir($newVideoDir);
            mkdir($newVideoThumbsDir);
            mkdir($vewVideoLogsDir);
            chmod($newVideoDir, 0755);
            chmod($newVideoThumbsDir, 0755);
            chmod($vewVideoLogsDir, 0755);
        }
        
        return $sql->id;
       
    }
    
    public function getVideoFromDB($id) {
        return $this::$database->table(self::TABLE_NAME)->get($id);
    }
    
    public function countVideos() {
        return $this::$database->table(self::TABLE_NAME)->count("*");
    }
    
    public function getVideosFromDB($from, $count, $order) {
        return $this::$database->table(self::TABLE_NAME)
                ->limit($from, $count)
                ->order($order);
    }
    
    public function getOriginalFileInfo($id) {
        $video = $this->getVideoFromDB($id);
        $finfo = finfo_open();
        $file = VIDEOS_FOLDER . $id ."/". $video->original_file;
        if (file_exists($file)) {
            $fileinfo = finfo_file($finfo, $file, FILEINFO_MIME);
            finfo_close($finfo);
            return $fileinfo;
        } else {
            return false;
        }
    }
    
    public function deleteVideoFile($id, $file) {
        $video = $this->getVideoFromDB($id);
        unlink(VIDEOS_FOLDER . $id ."/". $video->$file);
        $video->update(array($file => ""));
    }
    
    public function deleteVideo($id) {
        $video = $this->getVideoFromDB($id);
        FileUtils::recursiveDelete(VIDEOS_FOLDER . $id ."/");
        $video->delete();
    }
    
    public function useOriginalFileAs($id, $target) {
        $video = $this->getVideoFromDB($id);
        $video->update(array(self::COLUMN_ORIGINAL_FILE => "", $target => $video->original_file));
    }
}
