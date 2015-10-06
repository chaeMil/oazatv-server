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
    
    function __construct(Nette\Database\Context $database,
            VideoManager $videoManager) {
        $this->database = $database;
        $this->videoManager = $videoManager;
    }
    
    function actionMigrate($id) {
        $video = $this->videoManager->getVideoFromDB($id, 2);
        $this->template->video = $video;
        
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
       
        
        $this->template->videoNew = $videoNew;
    }
    
    function renderDefault() {
        
    }
}