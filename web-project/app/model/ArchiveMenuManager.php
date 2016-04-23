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

use Nette;
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

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
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
    
    
    public function getMenuFromDB($id) {
        return self::$database->table(self::TABLE_NAME)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id))
                ->fetch();
    }
    
    public function getMenusFromDB() {
        return self::$database->table(self::TABLE_NAME)
            ->select('*')
            ->fetchAll();

    }
    
    public function deleteMenu($id) {
        $video = $this->getMenuFromDB($id);
        $video->delete();
    }
    
    public function getLocalizedMenu($id, $lang) {
        $menu = $this->getMenuFromDB($id);
        
        $newMenu['id'] = $$menu['id'];
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
            $newMenu['color'] = $menu['color'];
            
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