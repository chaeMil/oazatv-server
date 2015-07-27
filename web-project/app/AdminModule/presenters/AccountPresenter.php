<?php
namespace App\AdminModule;

use Nette,
    Model,
    Nette\Forms\Form,
    App\Model\UserManager;

/**
 * Description of AccountPresenter
 *
 * @author Michal Mlejnek <chaemil72@gmail.com>
 */
class AccountPresenter extends BasePresenter {
    /**
     * DB functions for user manager
     * 
     * @var type Model\UserManager
     */
    private $userManager;
    public $database;

    /**
     * Constructor
     * 
     * @param Model\UserManager $userManager DB function for user management
     */
    function __construct(UserManager $userManager, Nette\Database\Context $database) {
        $this->userManager = $userManager;
        $this->database = $database;
    }
    
    /**
     * Check if user is logged in
     */
    function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect(':Sign:sign:');
        }
    }
    
    function renderDefault() {
        $this->getTemplateVariables($this->getUser()->getId());
    }
    
    /**
     * Create form for managing users own account
     * 
     * @return \Nette\Application\UI\Form Form
     */
    public function createComponentMyAccountForm() {
        $form = new Nette\Application\UI\Form;

        $form->addPassword('newPass', 'Nové heslo:')
                ->setRequired('Please enter your new password.')
                ->setAttribute("class", "form-control")
                ->addRule(Form::MIN_LENGTH, 'Heslo musí mít alespoň %d znaků', 8);
        
        $form->addPassword('rePass', 'Nové heslo znovu:')
                ->setRequired('Please retype your password.')
                ->setAttribute("class", "form-control")
                ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['newPass']);

        $form->addSubmit('save', null)
                ->setAttribute("class", "btn btn-primary btn-xl");

        $form->onSuccess[] = $this->myAccountFormSucceeded;
        
        $this->bootstrapFormRendering($form);
        
        return $form;
    }
    
    /**
     * 
     * @param type $form
     */
    public function myAccountFormSucceeded($form) {
        $vals = $form->getValues();
        $this->userManager->update(
                $this->getUser()->getId(), $vals->newPass
                );
        $this->flashMessage('Heslo úspěšně změněno');
        $this->redirect('Account:default');
    }
}