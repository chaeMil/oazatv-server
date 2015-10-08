<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 Model\VideoManager;

/**
 * Description of BugReport
 *
 * @author chaemil
 */
class AnalyticsManager {
    
    const
            TABLE_NAME = 'popular_videos',
            COLUMN_ID = 'id',
            COLUMN_DATETIME = 'datetime';
    
    /** @var Nette\Database\Context */
    private $database;
    private $videoManager;

    public function __construct(Nette\Database\Context $database,
     VideoManager $videoManager) {
        
        $this->database = $database;
        $this->videoManager = $videoManager;
    }
    
    public function addVideoToPopular($videoId) {
        $this->database->table(self::TABLE_NAME)->insert(array(
            self::COLUMN_ID => $videoId, self::COLUMN_DATETIME => date("Y-m-d H:i:s")
        ));
    }
    
    public function getPopularVideosIds($days = 7) {
        $date = strtotime("-".$days." day");
        $slqDate = date('Y-m-d H:i:s', $date);
        
        return $this->database->table(self::TABLE_NAME)
                ->select(self::COLUMN_ID)
                ->where(self::COLUMN_DATETIME <= $slqDate)
                ->group(self::COLUMN_ID)
                ->fetchAll();
    }
    
}