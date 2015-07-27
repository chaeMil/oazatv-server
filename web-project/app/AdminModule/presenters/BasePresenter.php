<?php

namespace App\AdminModule;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
  
    function startup() {
        parent::startup();
    }
    
    public $database;
    
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
        
    }
    
    public function getUserFromDb($user_id) {
        return $this->database->table(DB_ADMIN_PREFIX.'users')
                ->where('id', $user_id)->fetch();
    }
    
    protected function createTemplate($class = NULL)
    {
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
