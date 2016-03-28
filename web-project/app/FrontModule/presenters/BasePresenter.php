<?php

namespace App\FrontModule;

use Nette,
    App\StringUtils,
    App\Presenters\ErrorPresenter,
    WebLoader,
    CssMin,
    Nette\Utils\Finder,
    App\Services\WebDir;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {    
    
    public $database;
    public $lang;
    public $container;
    public $webDir;
    
    /** @persistent */
    public $locale;

    /** @var \Kdyby\Translation\Translator @inject */
    public $translator;
    
    public function __construct(Nette\DI\Container $container, 
            Nette\Database\Context $database) {
        parent::__construct();
        $this->database = $database;
        $this->container = $container;
        
    }
    
    public function beforeRender() {
        parent::beforeRender();
        $routerLang = $this->getParameter('locale');
        $this->setupLanguage($this->container, $this->translator->getLocale());
    }
    
    public function setupLanguage($container, $lang = null) {
        if ($lang != null) {
            $this->lang = $lang;
        } else {
            $langs = array('cs', 'en'); // app supported languages
            $httpRequest = $container->getByType('Nette\Http\Request');
            $this->lang = $httpRequest->detectLanguage($langs);
        }
    }
    
    public function injectWebDir(WebDir $webDir) {
        $this->webDir = $webDir;
    }
    
    protected function createComponentCss() {
        $dir = $this->webDir->getPath('/');
        $files = new WebLoader\FileCollection($this->webDir->getPath('/css'));
        $files->addFiles(array(
            $dir . '/bower_components/bootstrap/dist/css/bootstrap.css',
            $dir . '/bower_components/font-awesome/css/font-awesome.css',
            $dir . '/css/BootstrapXL.css',
            $dir . '/bower_components/flexslider/flexslider.css',
            $dir . '/bower_components/video.js/dist/video-js.css',
            $dir . '/bower_components/photoswipe/dist/photoswipe.css',
            $dir . '/bower_components/photoswipe/dist/default-skin/default-skin.css',
            $dir . '/bower_components/justifiedGallery/dist/css/justifiedGallery.css',
            ));

        $files->addWatchFiles(Finder::findFiles('*.css', '*.less')->in($dir . '/bower_components/'));

        $compiler = WebLoader\Compiler::createCssCompiler($files, $dir . '/webtemp');

        /*$compiler->addFilter(new WebLoader\Filter\VariablesFilter(array('foo' => 'bar')));
        $compiler->addFilter(function ($code) {
            return cssmin::minify($code, "remove-last-semicolon");
        });*/

        $control = new WebLoader\Nette\CssLoader($compiler, $this->template->basePath. '/webtemp');
        $control->setMedia('screen');

        return $control;
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
