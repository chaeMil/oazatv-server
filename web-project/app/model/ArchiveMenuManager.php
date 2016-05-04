<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CategoriesManager
 *
 * @author Michal Mlejnek <chaemil72 at gmail.com>
 */
namespace Model;

use Nette,
 Model\VideoManager;
/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class ArchiveMenuManager extends BaseModel {

    const
            TABLE_NAME = 'db_archive_menu',
            COLUMN_ID = 'id',
            COLUMN_NAME_CS = 'name_cs',
            COLUMN_NAME_EN = 'name_en',
            COLUMN_SORT = 'sort',
            COLUMN_TAGS = 'tags',
            COLUMN_VISIBLE = 'visible';

    /** @var Nette\Database\Context */
    public static $database;
    private $videoManager;

    public function __construct(Nette\Database\Context $database,
            VideoManager $videoManager) {
        self::$database = $database;
        $this->videoManager = $videoManager;
    }
    
    private function checkIfMenuExists($id) {
        return self::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }
    
    public function saveMenuToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfMenuExists($id) > 0) {
            $category = self::$database->table(self::TABLE_NAME)->get($id);
            $sql = $category->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME)->insert($values);
        }

        return $sql->id;

    }
    
    
    public function getMenuFromDB($id, $visible = 1) {
        if ($visible != 2) {
            return self::$database->table(self::TABLE_NAME)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id, self::COLUMN_VISIBLE => $visible))
                ->fetch();
        } else {
            return self::$database->table(self::TABLE_NAME)
                    ->select("*")
                    ->where(array(self::COLUMN_ID => $id))
                    ->fetch();
        }
        
    }
    
    public function getMenuFromDBByTags($tags) {
        return self::$database
                    ->query("SELECT * FROM ".self::TABLE_NAME." WHERE ".self::COLUMN_TAGS." LIKE '%"
                            .$tags."%'")
                    ->fetch();
    }
    
    public function getMenusFromDB($visible = 1) {
        if ($visible != 2) {
            return self::$database->table(self::TABLE_NAME)
                ->where(array(self::COLUMN_VISIBLE => $visible))
                ->select('*')
                ->fetchAll();
        } else {
            return self::$database->table(self::TABLE_NAME)
                ->select('*')
                ->fetchAll();
        }

    }
    
    public function deleteMenu($id) {
        $video = $this->getMenuFromDB($id, 2);
        $video->delete();
    }
    
    public function countVideosInMenuByTag($tags) {
        $videos = $this->videoManager->getVideosFromDBbyTags($tags, 0, 999);
        
        return count($videos);
    }
    
    public function getLocalizedMenu($id, $lang) {
        $menu = $this->getMenuFromDB($id);
        
        $newMenu['id'] = $$menu['id'];
        $newMenu['tags'] = $menu['tags'];
        
        switch($lang) {
            case 'cs':
                $newMenu['name'] = $$menu['name_cs'];
                break;
            case 'en':
                $newMenu['name'] = $$menu['name_en'];
                break;
            default:
                $newMenu['name'] = $$menu['name_en'];
                break;
        }
        return $newMenu;
    }
   
    public function getLocalizedMenus($lang) {
        
        $menusArray = array();
        $menus = $this->getMenusFromDB();
        
        foreach($menus as $menu) {
            
            $newMenu['id'] = $menu['id'];
            $newMenu['tags'] = $menu['tags'];
            
            switch($lang) {
                case 'cs':
                    $newMenu['name'] = $menu['name_cs'];
                    break;
                case 'en':
                    $newMenu['name'] = $menu['name_en'];
                    break;
                default:
                    $newMenu['name'] = $menu['name_en'];
                    break;
            }
            
            $menusArray[] = $newMenu;
            
        }
        
        return $menusArray;
        
                
    }
}