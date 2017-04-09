<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 08/04/2017
 * Time: 15:12
 */

namespace App\FrontModule;

use Model\UserManager;
use Nette;
use App\EventLogger;
use Kdyby;

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
        }
    }

    public function renderLogin() {
        $this->template->user = $this->getUser();
    }

    public function renderRegister() {

    }

    public function actionLogout() {
        if ($this->getUser()->isLoggedIn()) {
            $this->user->logout();
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
        $form = new Nette\Application\UI\Form;
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

    public function signInFormSucceeded($form) {
        $values = $form->getValues();

        $this->getUser()->setExpiration('20 minutes', TRUE);

        try {

            $this->getUser()->getStorage()->setNamespace('front');
            $this->getUser()->login($values->email, $values->password);

            $this->redirect('User:default:');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }

    public function createComponentRegister() {
        $form = new Nette\Application\UI\Form;
        $form->addText('email')
            ->setRequired('Please enter your username.')
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
}