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
class ConversionProfilesManager extends BaseModel {

    const
            TABLE_NAME = 'conversion_profiles',
            COLUMN_ID = 'id',
            COLUMN_NAME = 'name',
            COLUMN_AUDIO_BITRATE = 'audio_bitrate',
            COLUMN_VIDEO_BITRATE = 'video_bitrate';

    /** @var Nette\Database\Context */
    public static $database;

    public function __construct(Nette\Database\Context $database) {
        self::$database = $database;
    }
    
    private function checkIfProfileExists($id) {
        return self::$database->table(self::TABLE_NAME)
                ->where(self::COLUMN_ID, $id)->count();
    }
    
    public function saveProfileToDB($values) {

        if(isset($values['id'])) {
            $id = \Nette\Utils\Strings::webalize($values['id']);
        } else {
            $id = 0;
        }

        if ($id != 0 && $this->checkIfProfileExistsExists($id) > 0) {
            $profile = self::$database->table(self::TABLE_NAME)->get($id);
            $sql = $profile->update($values);
            return $sql;
        } else {
            unset($values['id']);
            $sql = self::$database->table(self::TABLE_NAME)->insert($values);
        }

        return $sql->id;

    }
    
    
    public function getProfileFromDB($id) {
        return self::$database->table(self::TABLE_NAME)
                ->select("*")
                ->where(array(self::COLUMN_ID => $id))
                ->fetch();
    }
    
    public function getProfilesFromDB() {
        return self::$database->table(self::TABLE_NAME)
            ->select('*')
            ->fetchAll();

    }
    
    public function deleteProfile($id) {
        $video = $this->getProfileFromDB($id);
        $video->delete();
    }
   
}