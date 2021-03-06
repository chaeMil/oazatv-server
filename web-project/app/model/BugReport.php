<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Model;

use Nette,
 Model\UserManager;

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
    
    public function getBugsFromDB($solved = 0) {
        switch($solved) {
            case 0:
                return $this::$database->table(self::TABLE_NAME)
                    ->select('*')->fetchAll();
                break;
            case 1:
                return $this::$database->table(self::TABLE_NAME)
                    ->select('*')->where(self::COLUMN_SOLVED, 1)
                    ->fetchAll();;
                break;
            case 2:
                return $this::$database->table(self::TABLE_NAME)
                    ->select('*')->where(self::COLUMN_SOLVED, 0)
                    ->fetchAll();
                break;
        }
        
    }
    
    public function countBugs($solved = 0) {
        switch($solved) {
            case 0:
                return $this::$database->table(self::TABLE_NAME)
                    ->select('*')->count();
                break;
            case 1:
            return $this::$database->table(self::TABLE_NAME)
                    ->select('*')->where(self::COLUMN_SOLVED, 1)
                    ->count();
                break;
            case 2:
                return $this::$database->table(self::TABLE_NAME)
                    ->select('*')->where(self::COLUMN_SOLVED, 0)
                    ->count();
                break;
        }
    }
}
