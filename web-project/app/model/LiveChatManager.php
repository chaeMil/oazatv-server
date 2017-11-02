<?php
/**
 * Created by PhpStorm.
 * User: Michal Mlejnek
 * Date: 02/11/2017
 * Time: 10:36
 */

namespace Model;

use Nette;

class LiveChatManager extends BaseModel {

    const
        TABLE_NAME = 'livechat',
        COLUMN_ID = 'id',
        COLUMN_DATETIME = 'datetime',
        COLUMN_APPROVED = 'approved',
        COLUMN_NAME = 'name',
        COLUMN_MESSAGE = 'message';

    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
    }

    public function createMessage($name, $message) {
        $data = array();
        $data[self::COLUMN_MESSAGE] = $message;
        $data[self::COLUMN_NAME] = $name;

        if (self::$database->table(self::TABLE_NAME)->insert($data)) {
            return true;
        }
        return false;
    }

    public function getAllMessages() {
        $messages = self::$database->table(self::TABLE_NAME)->fetchAll();
        return $messages;
    }

    public function getNonApprovedMessages($datetime = null) {
        if ($datetime == null) {
            $messages = self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_APPROVED => false))
                ->order(self::COLUMN_DATETIME.' DESC')
                ->fetchAll();
        } else {
            $messages = self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_APPROVED => false, self::COLUMN_DATETIME.' > ' .$datetime))
                ->fetchAll();
        }
        return $messages;
    }

    public function getApprovedMessages($datetime = null) {
        if ($datetime == null) {
            $messages = self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_APPROVED => true))
                ->order(self::COLUMN_DATETIME.' DESC')
                ->fetchAll();
        } else {
            $messages = self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->where(array(self::COLUMN_APPROVED => true, self::COLUMN_DATETIME.' > ' .$datetime))
                ->fetchAll();
        }
        return $messages;
    }

    public function approveMessage($id) {
        $message = self::$database->table(self::TABLE_NAME)->get($id);

        $data = array();
        $data[self::COLUMN_APPROVED] = true;

        return $message->update($data);
    }

    public function deleteMessage($id) {
        $message = self::$database->table(self::TABLE_NAME)->get($id);
        return $message->delete();
    }

    public function deleteAllMessages() {
        if (self::$database->table(self::TABLE_NAME)->delete()) {
            return true;
        }
        return false;
    }
}