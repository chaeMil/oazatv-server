<?php

namespace Model;

use Nette;

class AdminFacade extends BaseModel{
    /**
     * DB connection
     * @var Nette\Database\Context 
     */
    private $db;
    /**
    /**
     * Create connection with DB
     * @param Nette\Database\Context $db
     */
    function __construct() {
        $this->db = new Nette\Database\Context($db);
    }
}
