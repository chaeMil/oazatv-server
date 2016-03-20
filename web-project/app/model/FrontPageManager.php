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
class FrontPageManager extends BaseModel {

    const
            TABLE_NAME_ROWS = 'frontpage_rows',
            COLUMN_ID = 'id',
            COLUMN_PUBLISHED = 'published',
            COLUMN_NAME = 'name',
            COLUMN_BLOCKS = 'blocks';

    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
    }
    
    private function checkIfRowExists($id) {
        return self::$database->table(self::TABLE_NAME_ROWS)
                ->where(self::COLUMN_ID, $id)->count();
    }
    
    public function saveRowToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfRowExists($id) > 0) {
            $row = self::$database->table(self::TABLE_NAME_ROWS)->get($id);
            $sql = $$row->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME_ROWS)->insert($values);
        }

        return $sql->id;

    }
    
    
    public function getRowFromDB($id) {
        return self::$database->table(self::TABLE_NAME_ROWS)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id))
                ->fetch();
    }
    
    public function getRowsFromDB() {
        return self::$database->table(self::TABLE_NAME_ROWS)
            ->select('*')
            ->fetchAll();

    }
    
    public function deleteRow($id) {
        $video = $this->getRowFromDB($id);
        $video->delete();
    }
   
    

}