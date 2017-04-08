<?php

namespace Model;

use Nette,
    Nette\Utils\Strings,
    Nette\Security\Passwords;

/**
 * Users management.
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator {

    const
        TABLE_NAME = 'admin_users',
        COLUMN_ID = 'id',
        COLUMN_NAME = 'login',
        COLUMN_PASSWORD_HASH = 'password';

    const
        TABLE_NAME_USERS = 'db_users',
        COLUMN_EMAIL = 'email',
        COLUMN_FB_ID = 'fb_id',
        COLUMN_FB_TOKEN = 'fb_token',
        COLUMN_GPLUS_ID = 'gplus_id',
        COLUMN_GPLUS_TOKEN = 'gplus_token';

    /** @var Nette\Database\Context */
    public $database;

    public function __construct(Nette\Database\Context $database) {
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($username, $password) = $credentials;
        $password = self::removeCapsLock($password);
        $row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_NAME, $username)->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException('Nesprávný login nebo heslo.', self::IDENTITY_NOT_FOUND);
        } elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException('Nesprávný login nebo heslo.', self::INVALID_CREDENTIAL);
        } elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update(array(
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
            ));
        }

        //$roles = $this->database->table('role')->where('user_id', $row['id'])->fetch()->toArray();

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);

        return new Nette\Security\Identity($row[self::COLUMN_ID], 'administrator', $arr);
    }

    /**
     * Adds new user.
     * @param  string
     * @param  string
     * @return void
     */
    public function add($username, $password) {
        if ($this->checkIfUserExists($username) == 0) {
            $this->database->table(self::TABLE_NAME)->insert(array(
                self::COLUMN_NAME => $username,
                self::COLUMN_PASSWORD_HASH => Passwords::hash(self::removeCapsLock($password)),
            ));
            return true;
        } else {
            return false;
        }
        
    }
    
    private function checkIfUserExists($username) {
        return $this->database->table(self::TABLE_NAME)
                ->where(self::COLUMN_NAME, $username)->count();
    }
    
    public function delete($user_id) {
        $user = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $user_id);
        $user_avatar = ADMIN_UPLOADED_DIR."/avatars/".$user_id.".jpg";
        if(file_exists($user_avatar)) {
            unlink($user_avatar);
        }
        $user->delete();
    }

    /**
     * Update user.
     * @param  int
     * @param  string
     * @return void
     */
    public function update($user_id, $password) {
        $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $user_id)->update(array(
            self::COLUMN_PASSWORD_HASH => Passwords::hash(self::removeCapsLock($password)),
        ));
    }

    public function getUserFromDB($user_id) {
        return $this->database->table(self::TABLE_NAME)
                ->select('*')
                ->where(self::COLUMN_ID, $user_id)
                ->fetch();
    }

    public function getFrontUserFromDB($userId) {
        return $this->database->table(self::TABLE_NAME_USERS)
            ->select('*')
            ->where(self::COLUMN_ID, $userId)
            ->fetch();
    }

    /**
     * Fixes caps lock accidentally turned on.
     * @return string
     */
    private static function removeCapsLock($password) {
        return $password === Strings::upper($password) ? Strings::lower($password) : $password;
    }
    
    public static function createUserTempFolder($userId) {
        if (!file_exists("temp/users/".$userId)) {
            mkdir("temp/users/".$userId);
            chmod("temp/users/".$userId, 0777);
        }
    }
    
    public function emptyUserTempFolder($userId) {
        \App\FileUtils::recursiveDelete("temp/users/".$userId."/");
    }

    public function findByFacebookId($fbId) {
        $user = $this->database->table(self::TABLE_NAME_USERS)
            ->select('*')
            ->where(self::COLUMN_FB_ID, $fbId)
            ->fetch();

        return $user;
    }

    public function registerFromFacebook($fbId, $me) {
        return $this->database->table(self::TABLE_NAME_USERS)
            ->insert(array(self::COLUMN_FB_ID => $fbId,
                    self::COLUMN_EMAIL => $me['email']));
    }

    public function updateFacebookAccessToken($fbId, $accessToken) {
        return $this->database->table(self::TABLE_NAME_USERS)
            ->where(array(self::COLUMN_FB_ID => $fbId))
            ->update(array(self::COLUMN_FB_TOKEN => $accessToken));
    }

    public function findByGoogleId($googleId) {
        $user = $this->database->table(self::TABLE_NAME_USERS)
            ->select('*')
            ->where(self::COLUMN_GPLUS_ID, $googleId)
            ->fetch();

        return $user;
    }

    public function registerFromGoogle($googleId, $me) {
        dump($googleId, $me); exit;
    }

}
