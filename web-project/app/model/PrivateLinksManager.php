<?php
/**
 * Created by PhpStorm.
 * User: chaemil
 * Date: 25.1.17
 * Time: 13:53
 */

namespace Model;

use Nette;

class PrivateLinksManager extends BaseModel {

    const
        TABLE_NAME = 'db_private_links',
        COLUMN_ID = 'id',
        COLUMN_ITEM_HASH = 'item_hash',
        COLUMN_VALID = 'valid',
        COLUMN_PASS = 'pass';

    /** @var Nette\Database\Context */
    public static $database;
    public static $queueManager;
    private $videoManager;
    private $photosManager;

    public function __construct(Nette\Database\Context $database,
                                VideoConvertQueueManager $queueManager,
                                VideoManager $videoManager,
                                PhotosManager $photosManager) {
        self::$database = $database;
        self::$queueManager = $queueManager;
        $this->videoManager = $videoManager;
        $this->photosManager = $photosManager;
    }

    private function checkIfLinkExists($id) {
        return self::$database->table(self::TABLE_NAME)
            ->where(self::COLUMN_ID, $id)->count();
    }

    public function saveToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfLinkExists($id) > 0) {
            $item = self::$database->table(self::TABLE_NAME)->get($id);
            $sql = $item->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME)->insert($values);
        }

        return $sql->id;
    }

    public function getFromDBById($id) {
        return self::$database->table(self::TABLE_NAME)
            ->select("*")
            ->where(array(self::COLUMN_ID => $id))
            ->fetch();
    }

    public function getFromDBbyHash($hash) {
        return self::$database->table(self::TABLE_NAME)
            ->select("*")
            ->where(array(self::COLUMN_ITEM_HASH => $hash))
            ->fetchAll();
    }

    public function delete($id) {
        $video = $this->getFromDBbyId($id);
        $video->delete();
    }

    public function getAll() {
        return self::$database->table(self::TABLE_NAME)
            ->select("*")
            ->fetchAll();
    }

    public function getItem($hash) {
        $video = $this->videoManager->getVideoFromDBbyHash($hash, 2);
        $album = $this->photosManager->getAlbumFromDBbyHash($hash, 2);

        if ($video != false) {
            $video = $video->toArray();
            $video['type'] = 'video';
            return $video;
        }

        if ($album != false) {
            $album = $album->toArray();
            $album['type'] = 'album';
            return $album;
        }
    }

    public function isValid($id) {
        $link = $this->getFromDBById($id);
        if ($link != false) {
            $now = time();
            $target = strtotime($link[self::COLUMN_VALID]);
            $diff = $now - $target;
            return $diff <= 0;
        }
        return false;
    }

    public function validate($hash, $pass) {
        $privateLink = self::$database->table(self::TABLE_NAME)
            ->select("*")
            ->where(array(self::COLUMN_ITEM_HASH => $hash, self::COLUMN_PASS => $pass))
            ->fetch();

        return $privateLink != false && $this->isValid($privateLink[self::COLUMN_ID]);
    }

}