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
            TABLE_NAME_POPULAR_VIDEOS = 'popular_videos',
            COLUMN_ID = 'id',
            COLUMN_DATETIME = 'datetime',
            COUNT = 'count',
            TABLE_NAME_VIDEO_ANALYTICS = 'db_video_analytics',
            COLUMN_VIDEO = 'video',
            COLUMN_ACTION = 'action',
            COLUMN_ACTION_TYPE = 'action_type',
            WATCH = 'watch',
            SHARE = 'share',
            WEB = 'web',
            API = 'api',
            SEARCH_CLICK = 'search_click',
            TABLE_NAME_ANALYTICS_ALIVE_USERS = 'analytics_live_users',
            COLUMN_OAZA_USER_ID = 'oaza_user_id',
            COLUMN_ALIVE = 'alive',
            COLUMN_IP = 'ip',
            COLUMN_OS = 'os',
            COLUMN_BROWSER = 'browser',
            COLUMN_PAGE = 'page';
    
    /** @var Nette\Database\Context */
    private $database;
    private $videoManager;

    public function __construct(Nette\Database\Context $database,
     VideoManager $videoManager) {
        
        $this->database = $database;
        $this->videoManager = $videoManager;
    }
    
    public function addVideoToPopular($videoId) {
        $this->database->table(self::TABLE_NAME_POPULAR_VIDEOS)->insert(array(
            self::COLUMN_ID => $videoId, self::COLUMN_DATETIME => date("Y-m-d H:i:s")
        ));
    }
    
    public function getPopularVideosIds($days = 7, $limit = 10) {
        $date = strtotime("-".$days." day");
        $sqlDate = date('Y-m-d H:i:s', $date);
        
        $videos = $this->database
                ->query('SELECT '.self::COLUMN_ID.', COUNT(*) '.self::COUNT.
                        ' FROM '.self::TABLE_NAME_POPULAR_VIDEOS.
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
    
    public function countVideoView($videoId, $type) {
        return $this->database->table(self::TABLE_NAME_VIDEO_ANALYTICS)
                ->insert(array(
                    self::COLUMN_VIDEO => $videoId, 
                    self::COLUMN_ACTION => self::WATCH,
                    self::COLUMN_ACTION_TYPE => $type,
                    self::COLUMN_DATETIME => new Nette\Database\SqlLiteral('NOW()')
                ));
    }
    
    public function countVideoSearchClick($videoId, $type) {
        return $this->database->table(self::TABLE_NAME_VIDEO_ANALYTICS)
                ->insert(array(
                    self::COLUMN_VIDEO => $videoId,
                    self::COLUMN_ACTION => self::SEARCH_CLICK,
                    self::COLUMN_ACTION_TYPE => $type,
                    self::COLUMN_DATETIME => new Nette\Database\SqlLiteral('NOW()')
                ));
    }
    
    public function updateAliveUser($oazaUserId, $ip, $os, $browser, $page) {
        if (isset($oazaUserId) && isset($ip) && isset($os) && isset($browser) && isset($page)) {
            $query = "REPLACE INTO ".self::TABLE_NAME_ANALYTICS_ALIVE_USERS."
                    (".self::COLUMN_OAZA_USER_ID.",".
                    self::COLUMN_IP.",".
                    self::COLUMN_OS.",".
                    self::COLUMN_BROWSER.",".
                    self::COLUMN_PAGE.")".
                    " VALUES ".
                    "('".htmlentities($oazaUserId)."','".htmlentities($ip)."','".htmlentities($os)
                    ."','".htmlentities($browser)."','".htmlentities($page)."')";
            return $this->database->query($query);
        } else {
            return false;
        }
    }
    
    public function getAliveUsersFromPage($page, $minutes, $adminUserOazaId) {
        return $this->database->table(self::TABLE_NAME_ANALYTICS_ALIVE_USERS)
                ->select('*')
                ->where(self::COLUMN_PAGE.' LIKE ?', '%'.$page.'%')
                ->where(self::COLUMN_ALIVE.' <= ?', time() - 60 * $minutes)
                ->where(self::COLUMN_OAZA_USER_ID.' != ?', $adminUserOazaId)
                ->fetchAll();
    }
    
}