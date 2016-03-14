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
Model\TagsManager,
App\ImageUtils;
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
            COLUMN_ABOUT_EN = 'about_en',
            THUMB_2048 = 2048,
            THUMB_1024 = 1024,
            THUMB_512 = 512,
            THUMB_256 = 256,
            THUMB_128 = 128;

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

    public function deletePreacher($id) {
        $preacher = $this->getPreacherFromDB($id);
        if (file_exists(PREACHERS_FOLDER.$id.".jpg")) {
            unlink(PREACHERS_FOLDER.$id.".jpg");
        }
        $this->deletePhotoThumbnails($id);
        $preacher->delete();
    }
    
    public function getPhotoThumbnails($id) {
        $photo = $id.".jpg";
        $thumb = PREACHERS_FOLDER.str_replace(".jpg", "_".self::THUMB_1024.".jpg", $photo);
        $thumbfile = PREACHERS_FOLDER.str_replace(".jpg", "", $photo);
        if (!file_exists($thumb)) {
            $this->generatePhotoThumbnails($id);
        }
        return array(self::THUMB_2048 => $thumbfile."_".self::THUMB_2048.".jpg",
                self::THUMB_1024 => $thumbfile."_".self::THUMB_1024.".jpg",
                self::THUMB_512 => $thumbfile."_".self::THUMB_512.".jpg",
                self::THUMB_256 => $thumbfile."_".self::THUMB_256.".jpg",
                self::THUMB_128 => $thumbfile."_".self::THUMB_128.".jpg");
    }
    
    public function generatePhotoThumbnails($id) {
        if (file_exists(PREACHERS_FOLDER.$id.".jpg")) {

            ImageUtils::resizeImage(PREACHERS_FOLDER, $id.".jpg", self::THUMB_2048, self::THUMB_2048, PREACHERS_FOLDER);
            ImageUtils::resizeImage(PREACHERS_FOLDER, $id.".jpg", self::THUMB_1024, self::THUMB_1024, PREACHERS_FOLDER);
            ImageUtils::resizeImage(PREACHERS_FOLDER, $id.".jpg", self::THUMB_512, self::THUMB_512, PREACHERS_FOLDER);
            ImageUtils::resizeImage(PREACHERS_FOLDER, $id.".jpg", self::THUMB_256, self::THUMB_256, PREACHERS_FOLDER);
            ImageUtils::resizeImage(PREACHERS_FOLDER, $id.".jpg", self::THUMB_128, self::THUMB_128, PREACHERS_FOLDER);
        }
    }

    public function deletePhotoThumbnails($id) {
        foreach($this->getPhotoThumbnails($id) as $thumbnail) {
            dump($thumbnail);
            if (file_exists($thumbnail)) {
                unlink($thumbnail);
            }
        }
    }

}
