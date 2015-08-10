<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 App\Model\UserManager;

/**
 * Description of BugReport
 *
 * @author chaemil
 */
class BugReport {
    
    const
            TABLE_NAME = 'bug_report',
            COLUMN_ID = 'id',
            COLUMN_USER_ID = 'user_id',
            COLUMN_BUG_NAME = 'bug_name',
            COLUMN_BUG_DESC = 'bug_desc',
            COLUMN_PRIORITY = 'priority',
            COLUMN_SOLVED = 'solved';
    
    /** @var Nette\Database\Context */
    public static $database;
    private static $userManager;

    public function __construct(Nette\Database\Context $database, 
            \Model\VideoManager $videoManager, UserManager $userManager) {
        $this::$database = $database;
        $this::$userManager = $userManager;
    }
    
    public function reportBug($values) {
        $this::$database->table(self::TABLE_NAME)->insert($values);
    }
    
    public function deleteBug($id) {
        $bug = $this::$database->table(self::TABLE_NAME)->get($id);
        $bug->delete();
    }
    
    public function getBugFromDB($id) {
        return $this::$database->table(self::TABLE_NAME)->get($id);
    }
    
    public function getBugsFromDB($from, $count, $order) {
        return $this::$database->table(self::TABLE_NAME)
                ->limit($from, $count)
                ->order($order);
    }
    
    public function countBugs() {
        return $this::$database->table(self::TABLE_NAME)->count("*");
    }
}
