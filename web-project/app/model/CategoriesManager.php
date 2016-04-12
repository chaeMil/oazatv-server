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
class CategoriesManager extends BaseModel {

    const
            TABLE_NAME = 'db_video_categories',
            COLUMN_ID = 'id',
            COLUMN_NAME_CS = 'name_cs',
            COLUMN_NAME_EN = 'name_en';

    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
    }
    
    private function checkIfCategoryExists($id) {
        return self::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }
    
    public function saveCategoryToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfCategoryExists($id) > 0) {
            $category = self::$database->table(self::TABLE_NAME)->get($id);
            $sql = $category->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME)->insert($values);
        }

        return $sql->id;

    }
    
    
    public function getCategoryFromDB($id) {
        return self::$database->table(self::TABLE_NAME)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id))
                ->fetch();
    }
    
    public function getCategoriesFromDB() {
        return self::$database->table(self::TABLE_NAME)
            ->select('*')
            ->fetchAll();

    }
    
    public function deleteCategory($id) {
        $video = $this->getCategoryFromDB($id);
        $video->delete();
    }
    
    public function getLocalizedCategory($id, $lang) {
        $category = $this->getCategoryFromDB($id);
        
        $newCategory['id'] = $category['id'];
        switch($lang) {
            case 'cs':
                $newCategory['name'] = $category['name_cs'];
                break;
            case 'en':
                $newCategory['name'] = $category['name_en'];
                break;
            default:
                $newCategory['name'] = $category['name_en'];
                break;
        }
        return $newCategory;
    }
   
    public function getLocalizedCategories($lang) {
        
        $categoriesArray = array();
        $categories = $this->getCategoriesFromDB();
        
        foreach($categories as $category) {
            
            $newCategory['id'] = $category['id'];
            
            switch($lang) {
                case 'cs':
                    $newCategory['name'] = $category['name_cs'];
                    break;
                case 'en':
                    $newCategory['name'] = $category['name_en'];
                    break;
                default:
                    $newCategory['name'] = $category['name_en'];
                    break;
            }
            
            $categoriesArray[] = $newCategory;
            
        }
        
        return $categoriesArray;
        
                
    }
}