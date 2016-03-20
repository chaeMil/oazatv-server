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
            TABLE_NAME_BLOCKS = 'frontpage_blocks',
            COLUMN_ID = 'id',
            COLUMN_SORT = 'sort',
            COLUMN_PUBLISHED = 'published',
            COLUMN_NAME = 'name',
            COLUMN_BLOCKS = 'blocks',
            COLUMN_TYPE = 'type',
            COLUMN_JSON_DATA = 'json_data';

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
            $sql = $row->update($values);
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
        $row = $this->getRowFromDB($id);
        $row->delete();
    }
    
    private function checkIfBlockExists($id) {
        return self::$database->table(self::TABLE_NAME_BLOCKS)
                ->where(self::COLUMN_ID, $id)->count();
    }
    
    public function saveBlockToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfBlockExists($id) > 0) {
            $block = self::$database->table(self::TABLE_NAME_BLOCKS)->get($id);
            $sql = $block->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME_BLOCKS)->insert($values);
        }

        return $sql->id;

    }
   
    public function getBlockFromDB($id) {
        return self::$database->table(self::TABLE_NAME_BLOCKS)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id))
                ->fetch();
    }
    
    public function getBlocksFromDB() {
        return self::$database->table(self::TABLE_NAME_BLOCKS)
            ->select('*')
            ->order(self::COLUMN_SORT." ASC")
            ->fetchAll();

    }
    
    public function deleteBlock($id) {
        $row = $this->getBlockFromDB($id);
        $row->delete();
    }
    
    public function getBlocksFromRow($rowId) {
        $row = $this->getRowFromDB($rowId);
        $blocksIdsArray = explode(",", str_replace(" ", "", trim($row)));

    }
    
    public function isBlockInRow($blockId, $rowId) {
        $row = $this->getRowFromDB($rowId);
        $blockIdsArray = $blocksIdsArray = explode(",", str_replace(" ", "", trim($row)));
        return in_array($blockId, $blockIdsArray);
    }

}