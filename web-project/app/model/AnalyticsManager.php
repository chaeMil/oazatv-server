<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 Model\VideoManager,
 Nette\Utils\Strings;

/**
 * Description of BugReport
 *
 * @author chaemil
 */
class AnalyticsManager {
    
    const
            TABLE_NAME = 'popular_videos',
            COLUMN_ID = 'id',
            COLUMN_DATETIME = 'datetime',
            COUNT = 'count';
    
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
    
    public function getPopularVideosIds($days = 7, $limit = 10) {
        $date = strtotime("-".$days." day");
        $sqlDate = date('Y-m-d H:i:s', $date);
        
        $videos = $this->database
                ->query('SELECT '.self::COLUMN_ID.', COUNT(*) '.self::COUNT.
                        ' FROM '.self::TABLE_NAME.
                        ' WHERE '.self::COLUMN_DATETIME.' >= "'.$sqlDate.'"'.
                        ' GROUP BY '.self::COLUMN_ID.
                        ' ORDER BY '.self::COUNT.' DESC'.
                        ' LIMIT '.Strings::webalize($limit))->fetchAll();
        
        $videosArray = null;
        
        foreach($videos as $video) {
            $videosArray[] = $video->id;
        }
        
        return $videosArray;
    }
    
}