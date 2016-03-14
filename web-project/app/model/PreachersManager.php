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
Model\TagsManager;
/**
 * Description of VideoManager
 *
 * @author chaemil
 */
class PreachersManager extends BaseModel {

    const
            TABLE_NAME = 'db_preachers',
            COLUMN_ID = 'id',
            COLUMN_NAME = 'name',
            COLUMN_TAGS = 'tags',
            COLUMN_ABOUT_CS = 'about_cs',
            COLUMN_ABOUT_EN = 'about_en';

    /** @var Nette\Database\Context */
    public static $database;
    private $tagsManager;

    public function __construct(Nette\Database\Context $database,
    TagsManager $tagsManager) {
        self::$database = $database;
        $this->tagsManager = $tagsManager;
    }

    private function checkIfPreacherExists($id) {
        return self::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }

    public function savePreacherToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfPreacherExists($id) > 0) {
            $category = self::$database->table(self::TABLE_NAME)->get($id);
            $sql = $category->update($values);
            return $sql;
        } else {
            $sql = self::$database->table(self::TABLE_NAME)->insert($values);
        }

        return $sql->id;

    }


    public function getPreacherFromDB($id) {
        return self::$database->table(self::TABLE_NAME)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id))
                ->fetch();
    }

    public function getPreachersFromDB() {
        return self::$database->table(self::TABLE_NAME)
            ->select('*')
            ->order(self::COLUMN_NAME." COLLATE utf8_czech_ci")
            ->fetchAll();

    }

    public function deleteSPreacher($id) {
        $video = $this->getPreacherFromDB($id);
        $video->delete();
    }

}
