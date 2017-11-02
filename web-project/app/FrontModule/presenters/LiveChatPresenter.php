<?php
/**
 * Created by PhpStorm.
 * User: Michal Mlejnek
 * Date: 02/11/2017
 * Time: 14:50
 */

namespace App\FrontModule;

use Nette,
    Nette\Database\Context,
    Model\LiveChatManager;

class LiveChatPresenter extends BasePresenter {

    public $lang;
    public $container;
    public $liveChatManager;

    public function __construct(Nette\DI\Container $container,
                                Context $database, LiveChatManager $liveChatManager) {

        parent::__construct($container, $database);
        $this->liveChatManager = $liveChatManager;
    }

    public function renderDefault() {
        $messages = $this->liveChatManager->getApprovedMessages();
        $this->template->messages = $messages;
    }

    public function actionGetMessages() {
        $lastVisibleId = $_POST['lastVisibleId'];
        $messages = $this->liveChatManager->getApprovedMessages($lastVisibleId);
        $this->template->messages = $messages;
    }

}