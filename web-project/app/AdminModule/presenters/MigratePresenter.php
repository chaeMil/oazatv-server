<?php

namespace App\AdminModule;

use Nette,
 Model\VideoManager;

/**
 * Description of VideoPresenter
 *
 * @author chaemil
 */
class MigratePresenter extends BasePresenter {
    public $database;
    public $videoManager;
    public $videoFolderPrefix = 'db/videos/';
    
    function __construct(Nette\Database\Context $database,
            VideoManager $videoManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
    }
    
    function actionMigrate($id) {
        $video = $this->videoManager->getVideoFromDB($id, 2);
        $this->template->video = $video;
        $maxVideoId = $this->videoManager->countVideos();
        $this->template->maxVideoId = $maxVideoId;
        $this->template->previousVideoId = $video['id'] - 1;
        $nextVideoId = $video['id'] + 1;
        
        if ($this->videoManager->getVideoFromDB($nextVideoId)) {
            $this->template->nextVideoId = $nextVideoId;
        } else {
            $this->template->nextVideoId = $nextVideoId + 1;
        }

        $videoNew['id'] = $video['id'];
        $videoNew['published'] = $video['published'];
        $videoNew['original_file'] = '';
        
        $hash = substr($video['original_file'], 11, 6);
        $videoNew['hash'] = $hash;
        
        if (strpos($video['original_file'],'.flv') !== false) {
            $videoNew['original_file'] = $video['original_file'];
        }
        
        if (strpos($video['original_file'],'.mp4') !== false) {
            $videoNew['mp4_file'] = $video['original_file'];
        } else {
            $videoNew['mp4_file'] = '';
        }
        
        $mp4File = substr($video['original_file'], 0, -4).'.mp4';
        
        if (file_exists($this->videoFolderPrefix.$mp4File)) {
            $videoNew['mp4_file'] = $mp4File;
        } else {
            $videoNew['mp4_file'] = '';
        }
        
        $webmFile = substr($video['original_file'], 0, -4).'.webm';
        
        if (file_exists($this->videoFolderPrefix.$webmFile)) {
            $videoNew['webm_file'] = $webmFile;
        } else {
            $videoNew['webm_file'] = '';
        }
        
        $mp3File = substr($video['original_file'], 0, -4).'.mp3';
        
        if (file_exists($this->videoFolderPrefix.$mp3File)) {
            $videoNew['mp3_file'] = $mp3File;
        } else {
            $videoNew['mp3_file'] = '';
        }
        
        $thumbFile = substr($video['original_file'], 0, -4).'.jpg';
        
        if (file_exists($this->videoFolderPrefix.$thumbFile)) {
            $videoNew['thumb_file'] = $thumbFile;
        } else {
            $videoNew['thumb_file'] = '';
        }
        
        $videoNew['date'] = $video['date'];
        $videoNew['name_cs'] = $video['name_cs'];
        $videoNew['name_en'] = $video['name_en'];
        $videoNew['tags'] = $video['tags'];
        $videoNew['views'] = $video['views'];
        $videoNew['categories'] = $video['categories'];
        $videoNew['tags'] = $video['tags'];
        $videoNew['description_cs'] = $video['description_cs'];
        $videoNew['description_en'] = $video['description_en'];
        $videoNew['note'] = $video['note'];
        
        
        $this->template->videoNew = $videoNew;
    }
    
    function renderDefault() {
        
    }
}