<?php

namespace App\AdminModule;

use Nette,
 Model\VideoManager,
 App\StringUtils;

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

        $this->template->nextVideoId = $nextVideoId;

        $videoNew['id'] = $video['id'];
        $videoNew['published'] = $video['published'];
        $videoNew['original_file'] = '';
        
        $hash = substr($video['original_file'], 11, 6);
        $videoNew['hash'] = $hash;
        
        if (!file_exists($this->videoFolderPrefix.$videoNew['id'])) {
            mkdir($this->videoFolderPrefix.$videoNew['id']);
            mkdir($this->videoFolderPrefix.$videoNew['id'].'/thumbs');
            mkdir($this->videoFolderPrefix.$videoNew['id'].'/logs');
        }
        
        if (strpos($video['original_file'],'.flv') !== false) {
            
            $newFileName = StringUtils::rand(6).'.flv';
            rename($this->videoFolderPrefix.$video['original_file'], 
                    $this->videoFolderPrefix.$videoNew['id'].'/'.$newFileName);
            
            $videoNew['original_file'] = $newFileName;
            $newFileName = null;
        }
        
        $mp4File = substr($video['original_file'], 0, -4).'.mp4';
        
        if (strpos($video['original_file'],'.mp4') !== false) {            
            
            $newFileName = StringUtils::rand(6).'.mp4';
            rename($this->videoFolderPrefix.$mp4File, 
                    $this->videoFolderPrefix.$videoNew['id'].'/'.$newFileName);
            
            $videoNew['mp4_file'] = $newFileName;
            $videoNew['original_file'] = '';
            $newFileName = null;
            
        } else if (file_exists ($this->videoFolderPrefix.$mp4File)){
            $newFileName = StringUtils::rand(6).'.mp4';
            rename($this->videoFolderPrefix.$mp4File, 
                    $this->videoFolderPrefix.$videoNew['id'].'/'.$newFileName);
            
            $videoNew['mp4_file'] = $newFileName;
            $newFileName = null;
        } else {
            $videoNew['mp4_file'] = '';
        }       
        
        $webmFile = substr($video['original_file'], 0, -4).'.webm';
        
        if (file_exists($this->videoFolderPrefix.$webmFile)) {
            
            $newFileName = StringUtils::rand(6).'.webm';
            rename($this->videoFolderPrefix.$webmFile, 
                    $this->videoFolderPrefix.$videoNew['id'].'/'.$newFileName);
            
            $videoNew['webm_file'] = $newFileName;
            $newFileName = null;
        } else {
            $videoNew['webm_file'] = '';
        }
        
        $mp3File = substr($video['original_file'], 0, -4).'.mp3';
        
        if (file_exists($this->videoFolderPrefix.$mp3File)) {
            
            $newFileName = StringUtils::rand(6).'.mp3';
            rename($this->videoFolderPrefix.$mp3File, 
                    $this->videoFolderPrefix.$videoNew['id'].'/'.$newFileName);
            
            $videoNew['mp3_file'] = $newFileName;
            $newFileName = null;
        } else {
            $videoNew['mp3_file'] = '';
        }
        
        $thumbFile = substr($video['original_file'], 0, -4).'.jpg';
        
        if (file_exists($this->videoFolderPrefix.$thumbFile)) {
            
            $newFileName = StringUtils::rand(6).'.jpg';
            rename($this->videoFolderPrefix.$thumbFile, 
                    $this->videoFolderPrefix.$videoNew['id'].'/'.$newFileName);
            
            $videoNew['thumb_file'] = $newFileName;
            $newFileName = null;
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
        
        $this->videoManager->saveVideoToDB($videoNew);
        
    }
    
    function renderDefault() {
        
    }
}