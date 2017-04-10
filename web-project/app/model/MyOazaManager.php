<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 10/04/2017
 * Time: 14:20
 */

namespace Model;

use Kdyby\Translation\Translator;
use Nette;

class MyOazaManager {

    const HISTORY_TABLE = "mo_history",
        VIDEOTIMES_TABLE = "mo_videotimes",
        FAVORITES_TABLE = "mo_favorites",
        ID = "id",
        USER_ID = "user_id",
        VIDEO_ID = "video_id",
        WATCHED = "watched",
        TIME = "time";


    /** @var Nette\Database\Context */
    public $database;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    public function __construct(Nette\Database\Context $database,
                                Translator $translator) {
        $this->translator = $translator;
        $this->database = $database;
    }

    public function addVideoToHistory($userId, $videoId) {
        $lastSavedVideoID = $this->database
            ->table(self::HISTORY_TABLE)
            ->where(array(self::USER_ID => $userId, self::VIDEO_ID => $videoId))
            ->order(self::ID." DESC")
            ->select('*')
            ->fetch();

        if ($lastSavedVideoID[self::VIDEO_ID] != $videoId) {
            $this->database
                ->table(self::HISTORY_TABLE)
                ->insert(array(self::USER_ID => $userId,
                    self::VIDEO_ID => $videoId,
                    self::WATCHED => null));
        } else {
            $this->database
                ->table(self::HISTORY_TABLE)
                ->where(array(self::USER_ID => $userId, self::VIDEO_ID => $videoId))
                ->update(array(self::WATCHED => null));
        }
    }

    public function removeVideoFromHistory($userId, $videoId) {
        return $this->database
            ->table(self::HISTORY_TABLE)
            ->where(array(self::USER_ID => $userId, self::VIDEO_ID => $videoId))
            ->delete();
    }

    public function getVideoHistory($userId, $from, $count) {
        return $this->database
            ->table(self::HISTORY_TABLE)
            ->limit($count, $from)
            ->where(array(self::USER_ID => $userId))
            ->select('*')
            ->order(self::WATCHED." DESC")
            ->fetchAll();
    }

    public function saveVideoTime($userId, $videoId, $time) {
        $existingVideo = $this->database
            ->table(self::VIDEOTIMES_TABLE)
            ->where(array(self::USER_ID => $userId, self::VIDEO_ID => $videoId))
            ->order(self::ID." DESC")
            ->select('*')
            ->fetch();

        if ($existingVideo[self::VIDEO_ID] != $videoId) {
            $this->database
                ->table(self::VIDEOTIMES_TABLE)
                ->insert(array(self::USER_ID => $userId,
                    self::VIDEO_ID => $videoId,
                    self::TIME => $time));

            return true;
        } else {
            $this->database
                ->table(self::VIDEOTIMES_TABLE)
                ->where(array(self::USER_ID => $userId, self::VIDEO_ID => $videoId))
                ->update(array(self::TIME => $time));

            return true;
        }
    }

    public function getVideoTime($userId, $videoId) {
        return $this->database
            ->table(self::VIDEOTIMES_TABLE)
            ->select(self::TIME)
            ->where(array(self::USER_ID => $userId, self::VIDEO_ID => $videoId))
            ->fetch()[self::TIME];
    }

}