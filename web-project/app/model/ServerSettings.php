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
        return $this::$database->table(self::TABLE_NAME)->fetchAll();
    }
    
    private function checkIfKeyExists($key) {
        return $this::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_KEY, $key)->count();
    }
    
    public function loadValue($key) {
        return $this::$database->table(self::TABLE_NAME)->where(self::COLUMN_KEY, $key)->fetch();
    }
    
    public function saveValue($key, $value) {
        if ($this->checkIfKeyExists($key) != 0) {
            $option = $this::$database->table(self::TABLE_NAME)->where(self::COLUMN_KEY, $key);
            $option->update(self::COLUMN_VALUE, $value);
        } else {
            $this::$database->table(self::TABLE_NAME)->insert(array(self::COLUMN_KEY => $key, self::COLUMN_VALUE => $value));
        }
        
    }
}
