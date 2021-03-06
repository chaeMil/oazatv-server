<?php

namespace App\AdminModule;

use Nette,
    App\StringUtils;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {    
    public $database;
    public $container;

    public function __construct(Nette\DI\Container $container, Nette\Database\Context $database) {
        $this->container = $container;
        $this->database = $database;
    }
    
    public function getUserFromDb($user_id) {
        return $this->database->table(DB_ADMIN_PREFIX.'users')
                ->where('id', $user_id)->fetch();
    }
    
    public function getTemplateVariables($user_id) {
        $this->template->user = $this->getUserFromDb($user_id);
        $this->template->rand = StringUtils::rand(5);
    }
    
    protected function createTemplate($class = NULL) {
        $template = parent::createTemplate($class);
        $template->userRoles = $this->getUser()->getRoles();
        $template->version = VERSION;
        return $template;
    }
    
    public function bootstrapFormRendering($form) {
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        // make form and controls compatible with Twitter Bootstrap
        $form->getElementPrototype()->class('form-horizontal');
        foreach ($form->getControls() as $control) {
                if ($control instanceof Controls\Button) {
                        $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                        $usedPrimary = TRUE;
                } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                        $control->getControlPrototype()->addClass('form-control');
                } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                        $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
                }
        }
    }

}
