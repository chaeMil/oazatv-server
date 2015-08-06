<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette;

/**
 * Description of ServerSettings
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
class ServerSettings extends BaseModel {
    
    const
            TABLE_NAME = 'server_settings',
            COLUMN_ID = 'id',
            COLUMN_KEY = 'key',
            COLUMN_VALUE = 'value';
    
    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        $this::$database = $database;
    }
 
    public function loadAllSettings() {
        return $this::$database->table(self::TABLE_NAME)->fetchAssoc(self::COLUMN_KEY, self::COLUMN_VALUE);
    }
    
    private function checkIfKeyExists($key) {
        return $this::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_KEY, $key)->count();
    }
    
    public function loadValue($key) {
        if ($this->checkIfKeyExists($key) != 0) {
            return $this::$database->table(self::TABLE_NAME)->where(self::COLUMN_KEY, $key)->fetch()->value;
        } else {
            return false;
        }
    }
    
    public function saveValue($key, $value) {
        if ($this->checkIfKeyExists($key) != 0) {
            $option = $this::$database->table(self::TABLE_NAME)->where(self::COLUMN_KEY, $key);
            $option->update(array(self::COLUMN_VALUE => $value));
        } else {
            $this::$database->table(self::TABLE_NAME)
                    ->insert(array(self::COLUMN_KEY => $key, self::COLUMN_VALUE =>$value));
        }
    }
    
    public function deleteKey($key) {
        if ($this->checkIfKeyExists($key) != 0) {
            $key = $this::$database->table(self::TABLE_NAME)
                    ->where(self::COLUMN_KEY, $key)->fetch();
            $key->delete();
        }
    }
}
