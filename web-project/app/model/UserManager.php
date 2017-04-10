<?php

namespace Model;

use Kdyby\Translation\Translator;
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
        COLUMN_FIRSTNAME = 'firstname',
        COLUMN_LASTNAME = 'lastname',
        COLUMN_FB_ID = 'fb_id',
        COLUMN_FB_TOKEN = 'fb_token',
        COLUMN_GPLUS_ID = 'gplus_id',
        COLUMN_GPLUS_TOKEN = 'gplus_token';

    /** @var Nette\Database\Context */
    public $database;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;

    public function __construct(Nette\Database\Context $database,
            Translator $translator) {
        $this->translator = $translator;
        $this->database = $database;
    }

    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials, $frontend = false) {
        list($username, $password) = $credentials;
        $password = self::removeCapsLock($password);

        if ($frontend) {
            $tableName = self::TABLE_NAME_USERS;
            $role = "user";
            $login = self::COLUMN_EMAIL;
        } else {
            $tableName = self::TABLE_NAME;
            $role = "administrator";
            $login = self::COLUMN_NAME;
        }

        $row = $this->database
            ->table($tableName)
            ->where($login, $username)
            ->fetch();

        if (!$row) {
            throw new Nette\Security\AuthenticationException(
                $this->translator->translate('frontend.message.login_error'),
                    self::IDENTITY_NOT_FOUND);
        } elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new Nette\Security\AuthenticationException(
                $this->translator->translate('frontend.message.login_error'),
                    self::INVALID_CREDENTIAL);
        } elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update(array(
                self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
            ));
        }

        $arr = $row->toArray();
        unset($arr[self::COLUMN_PASSWORD_HASH]);

        return new Nette\Security\Identity($row[self::COLUMN_ID], $role, $arr);
    }

    public function add($username, $password, $frontend = false, $firstname = null, $lastname = null) {

        if ($frontend) {
            $tableName = self::TABLE_NAME_USERS;
            $login = self::COLUMN_EMAIL;

            $data = array(
                self::COLUMN_FIRSTNAME => $firstname,
                self::COLUMN_LASTNAME => $lastname,
                $login => $username,
                self::COLUMN_PASSWORD_HASH => Passwords::hash(self::removeCapsLock($password)));

        } else {
            $tableName = self::TABLE_NAME;
            $login = self::COLUMN_NAME;

            $data = array(
                $login => $username,
                self::COLUMN_PASSWORD_HASH => Passwords::hash(self::removeCapsLock($password)));
        }

        if ($this->checkIfUserExists($username, $frontend) == 0) {
            return $this->database
                ->table($tableName)
                ->insert($data);
        } else {
            return false;
        }
        
    }
    
    private function checkIfUserExists($username, $frontend = false) {

        if ($frontend) {
            $tableName = self::TABLE_NAME_USERS;
            $login = self::COLUMN_EMAIL;
        } else {
            $tableName = self::TABLE_NAME;
            $login = self::COLUMN_NAME;
        }

        return $this->database
            ->table($tableName)
            ->where($login, $username)
            ->count();
    }
    
    public function delete($userId, $frontend = false) {
        if ($frontend) {
            $tableName = self::TABLE_NAME_USERS;
        } else {
            $tableName = self::TABLE_NAME;
        }

        $user = $this->database
            ->table($tableName)
            ->where(self::COLUMN_ID, $userId);

        if (!$frontend) {
            $user_avatar = ADMIN_UPLOADED_DIR . "/avatars/" . $userId . ".jpg";
            if (file_exists($user_avatar)) {
                unlink($user_avatar);
            }
        }

        $user->delete();
    }

    /**
     * Update user.
     * @param  int
     * @param  string
     * @return void
     */
    public function update($userId, $password, $frontend = false) {
        if ($frontend) {
            $tableName = self::TABLE_NAME_USERS;
        } else {
            $tableName = self::TABLE_NAME;
        }

        $this->database
            ->table($tableName)
            ->where(self::COLUMN_ID, $userId)
            ->update(array(
                self::COLUMN_PASSWORD_HASH => Passwords::hash(self::removeCapsLock($password)),
        ));
    }

    public function getUserFromDB($userId) {
        return $this->database->table(self::TABLE_NAME)
                ->select('*')
                ->where(self::COLUMN_ID, $userId)
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
                    self::COLUMN_FIRSTNAME => $me['first_name'],
                    self::COLUMN_LASTNAME => $me['last_name'],
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

    public function findByEmail($email) {
        $user = $this->database->table(self::TABLE_NAME_USERS)
            ->select('*')
            ->where(self::COLUMN_EMAIL, $email)
            ->fetch();

        return $user;
    }

}
