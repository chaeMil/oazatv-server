<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette;

/**
 * Description of VideoConvertQueueManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class VideoConvertQueueManager extends BaseModel {
    
    const TABLE_NAME = 'video_convert_queue',
            COLUMN_ID = 'id',
            COLUMN_VIDEO_ID = 'video_id',
            COLUMN_INPUT = 'input',
            COLUMN_TARGET = 'target',
            COLUMN_STATUS = 'status',
            COLUMN_PROGRESS = 'progress',
            COLUMN_ADDED = 'added',
            COLUMN_STARTED_AT = 'started_at',
            COLUMN_FINISHED_AT = 'finished_at',
            STATUS_WAITING = 0,
            STATUS_CONVERTING = 1,
            STATUS_FINISHED = 2;
    
    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        $this::$database = $database;
    }
    
    private function checkIfAlreadyExists($videoId, $inputFile, $target) {
        return $this::$database->table(self::TABLE_NAME)
                ->where(array(self::COLUMN_VIDEO_ID => $videoId,
                    self::COLUMN_INPUT => $inputFile,
                    self::COLUMN_TARGET => $target))->count();
    }
    
    public function addVideoToQueue($videoId, $inputFile, $target) {
        if ($this->checkIfAlreadyExists($videoId, $inputFile, $target) == 0) {
            $this::$database->table(self::TABLE_NAME)
                ->insert(array (self::COLUMN_VIDEO_ID => $videoId,
                    self::COLUMN_INPUT => $inputFile,
                    self::COLUMN_TARGET => $target));
        }
    }
    
    public function getQueue($order) {
        return $this::$database->table(self::TABLE_NAME)->order(self::COLUMN_ID."=?", $order);
    }
    
    public function getFirstVideoToConvert() {
        return $this::$database->table(self::TABLE_NAME)->order(self::COLUMN_ID."=?", "ASC")
                ->where(self::COLUMN_STATUS, self::STATUS_WAITING)->limit(1)->fetch();
    }
    
    public function getVideoFromQueue($videoId) {
        return $this::$database->table(self::TABLE_NAME)->where(self::COLUMN_VIDEO_ID, $videoId);
    }
    
    public function getQueueCount($status) {
        if(!isset($status)) {
            return $this::$database->table(self::TABLE_NAME)->count();
        } else {
            return $this::$database->table(self::TABLE_NAME)->where(self::COLUMN_STATUS, $status)->count();
        }
    }
}
