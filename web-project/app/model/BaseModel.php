<?php

namespace Model;

use Nette;

class BaseModel {
    static public $database;

    static function init($database) {
    self::$database = new Nette\Database\Connection(
            $database->dsn, $database->user, $database->password);
    }
}