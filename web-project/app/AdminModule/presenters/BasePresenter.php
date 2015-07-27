<?php

namespace App\AdminModule;

use Nette,
	Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
  
    function startup() {
        parent::startup();
    }
    protected function createTemplate($class = NULL)
    {
        $template = parent::createTemplate($class);
        $template->userRoles = $this->getUser()->getRoles();
        return $template;
    }
}
