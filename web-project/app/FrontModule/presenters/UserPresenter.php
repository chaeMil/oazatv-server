<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 08/04/2017
 * Time: 15:12
 */

namespace App\FrontModule;

use Model\MyOazaManager;
use Model\UserManager;
use Nette;
use App\EventLogger;
use Kdyby;
use Nette\Application\UI\Form;
use Nette\Security\Identity;

class UserPresenter extends BasePresenter {

    /**
     * You can use whatever way to inject the instance from DI Container,
     * but let's just use constructor injection for simplicity.
     *
     * Class userManager is here only to show you how the process should work,
     * you have to implement it yourself.
     */

    public function renderDefault() {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(":login");
        } else {

            $user = $this->getUser();

            if (!$this->userManager->loadUserJson($user->getId())) {
                $this->redirect("wizard");
            }

            $history = $this->myOazaManager->getVideoHistory($user->getId(), 0, 6);
            $historyVideos = [];

            foreach ($history as $item) {
                $video = $this->videoManager->getVideoFromDB($item[MyOazaManager::VIDEO_ID], 1);
                if ($video) {
                    $localizedVideo = $this->videoManager->createLocalizedVideoObject($this->lang, $video);
                    $localizedVideo['watched'] = $item[MyOazaManager::WATCHED];
                    $historyVideos[] = $localizedVideo;
                }
            }

            $notes = $this->myOazaManager->getAllNotes($user->getId(), 0, 6);

            $this->template->historyVideos = $historyVideos;
            $this->template->notes = $notes;
        }
    }

    public function renderLogin() {
        $this->template->user = $this->getUser();
    }

    public function renderRegister() {

    }

    public function renderWizard() {

    }

    public function actionLogout() {
        $this->disallowAjax();

        if ($this->getUser()->isLoggedIn()) {
            $this->getUser()->logout();
        }

        $this->redirect(":login");
    }

    /** @return \Kdyby\Facebook\Dialog\LoginDialog */
    protected function createComponentFbLogin() {
        $dialog = $this->facebook->createDialog('login');
        /** @var \Kdyby\Facebook\Dialog\LoginDialog $dialog */

        $dialog->onResponse[] = function (\Kdyby\Facebook\Dialog\LoginDialog $dialog) {
            $fb = $dialog->getFacebook();

            if (!$fb->getUser()) {
                $this->flashMessage("Sorry bro, facebook authentication failed.");
                return;
            }

            /**
             * If we get here, it means that the user was recognized
             * and we can call the Facebook API
             */

            try {
                $me = $fb->api('/me', null,
                    ['fields' => ['id',
                        'first_name',
                        'last_name',
                        'picture',
                        'email']]);

                if (!$existing = $this->userManager->findByFacebookId($fb->getUser())) {
                    /**
                     * Variable $me contains all the public information about the user
                     * including facebook id, name and email, if he allowed you to see it.
                     */
                    if ($me['email'] != null) {
                        $existing = $this->userManager->registerFromFacebook($fb->getUser(), $me);
                    } else {
                        $this->error('Cannot get users email');
                    }

                }

                /**
                 * You should save the access token to database for later usage.
                 *
                 * You will need it when you'll want to call Facebook API,
                 * when the user is not logged in to your website,
                 * with the access token in his session.
                 */
                $this->userManager->updateFacebookAccessToken($fb->getUser(), $fb->getAccessToken());

                /**
                 * Nette\Security\User accepts not only textual credentials,
                 * but even an identity instance!
                 */
                $this->user->login(new \Nette\Security\Identity($existing->id, "user", $existing));

                /**
                 * You can celebrate now! The user is authenticated :)
                 */

            } catch (\Kdyby\Facebook\FacebookApiException $e) {
                /**
                 * You might wanna know what happened, so let's log the exception.
                 *
                 * Rendering entire bluescreen is kind of slow task,
                 * so might wanna log only $e->getMessage(), it's up to you
                 */
                \Tracy\Debugger::log($e, 'facebook');
                $this->flashMessage("Sorry bro, facebook authentication failed hard.");
            }

            $this->redirect('User:default');
        };

        return $dialog;
    }


    /** @return \Kdyby\Google\Dialog\LoginDialog */
    protected function createComponentGoogleLogin() {
        $dialog = new \Kdyby\Google\Dialog\LoginDialog($this->google);
        $dialog->onResponse[] = function (\Kdyby\Google\Dialog\LoginDialog $dialog) {
            $google = $dialog->getGoogle();

            if (!$google->getUser()) {
                $this->flashMessage("Sorry bro, google authentication failed.");
                return;
            }

            /**
             * If we get here, it means that the user was recognized
             * and we can call the Google API
             */

            try {
                $me = $google->getProfile();

                if (!$existing = $this->userManager->findByGoogleId($google->getUser())) {
                    /**
                     * Variable $me contains all the public information about the user
                     * including Google id, name and email, if he allowed you to see it.
                     */
                    $existing = $this->userManager->registerFromGoogle($google->getUser(), $me);
                }

                /**
                 * You should save the access token to database for later usage.
                 *
                 * You will need it when you'll want to call Google API,
                 * when the user is not logged in to your website,
                 * with the access token in his session.
                 */
                $this->userManager->updateGoogleAccessToken($google->getUser(), $google->getAccessToken());

                /**
                 * Nette\Security\User accepts not only textual credentials,
                 * but even an identity instance!
                 */
                $this->user->login(new \Nette\Security\Identity($existing->id, $existing->roles, $existing));

                /**
                 * You can celebrate now! The user is authenticated :)
                 */

            } catch (\Exception $e) {
                /**
                 * You might wanna know what happened, so let's log the exception.
                 *
                 * Rendering entire bluescreen is kind of slow task,
                 * so might wanna log only $e->getMessage(), it's up to you
                 */
                \Tracy\Debugger::log($e, 'google');
                $this->flashMessage("Sorry bro, google authentication failed hard.");
            }

            $this->redirect('User:default');
        };

        return $dialog;
    }

    protected function createComponentSignInForm() {
        $form = new Form;
        $form->addText('email')
            ->setRequired('Please enter your email.')
            ->setAttribute("placeholder", $this->translator->translate("frontend.basic.email"))
            ->setAttribute("class", "form-control");

        $form->addPassword('password')
            ->setRequired('Please enter your password.')
            ->setAttribute("placeholder", $this->translator->translate("frontend.basic.password"))
            ->setAttribute("class", "form-control");

        $form->addSubmit('send',
                $this->translator->translate('frontend.basic.login'))
            ->setAttribute("class", "btn-lg btn-success btn-block");

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = [$this, 'signInFormSucceeded'];

        // setup Bootstrap form rendering
        //$this->bootstrapFormRendering($form);

        return $form;
    }

    public function signInFormSucceeded(Form $form) {
        $this->login($form, $form->values);
    }

    public function createComponentRegister() {
        $form = new Form;

        $form->addText('firstname')
            ->setRequired('Please enter your firstname.')
            ->setAttribute("placeholder", $this->translator->translate("frontend.basic.firstname"))
            ->setAttribute("class", "form-control");

        $form->addText('lastname')
            ->setRequired('Please enter your lastname.')
            ->setAttribute("placeholder", $this->translator->translate("frontend.basic.lastname"))
            ->setAttribute("class", "form-control");

        $form->addEmail('email')
            ->setRequired('Please enter your email.')
            ->setAttribute("placeholder", $this->translator->translate("frontend.basic.email"))
            ->setAttribute("class", "form-control");

        $form->addPassword('password')
            ->setRequired('Please enter your password.')
            ->setAttribute("placeholder", $this->translator->translate("frontend.basic.password"))
            ->setAttribute("class", "form-control");

        $form->addSubmit('send',
            $this->translator->translate('frontend.basic.register'))
            ->setAttribute("class", "btn-lg btn-success btn-block");

        // call method signInFormSucceeded() on success
        $form->onSuccess[] = [$this, 'registerSucceeded'];

        // setup Bootstrap form rendering
        //$this->bootstrapFormRendering($form);

        return $form;
    }

    public function registerSucceeded(Form $form) {
        $values = $form->getValues();
        $status = $this->userManager->add($values->email, $values->password,
            true,
            $values->firstname,
            $values->lastname);

        if ($status) {
            $this->login($form, $values);
        } else {
            $form->addError($this->translator->translate("frontend.message.email_already_taken"));
        }

    }

    /**
     * @param $form
     * @param $values
     */
    public function login(Form $form, $values) {
        $this->disallowAjax();

        try {
            $this->getUser()->logout();
            $this->userManager->authenticate(array($values['email'], $values['password']), true);
            $user = $this->userManager->findByEmail($values['email']);
            $userFromDB = $this->userManager->getFrontUserFromDB($user[UserManager::COLUMN_ID]);
            $this->user->login(new Identity($user[UserManager::COLUMN_ID], "user", $userFromDB));
            $this->redirect('User:default');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}