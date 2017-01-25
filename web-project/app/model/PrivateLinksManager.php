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

    public function __construct(Nette\Database\Context $database,
                                VideoConvertQueueManager $queueManager) {
        self::$database = $database;
        self::$queueManager = $queueManager;
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

}