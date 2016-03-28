<?php

namespace App\FrontModule;

use Nette,
App\Services\WebDir,
WebLoader,
CssMin,
Nette\Utils\Finder;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {    
    
    public $database;
    public $lang;
    public $container;
    private $webDir;
    
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
        $dir = $this->webDir->getPath('');
        $files = new WebLoader\FileCollection($dir);
        $files->addFiles(array(
            $dir . '/assets/bower_components/bootstrap/dist/css/bootstrap.css',
            $dir . '/assets/css/BootstrapXL.css',
            $dir . '/assets/bower_components/flexslider/flexslider.css',
            $dir . '/assets/bower_components/video.js/dist/video-js.css',
            $dir . '/assets/bower_components/photoswipe/dist/photoswipe.css',
            $dir . '/assets/bower_components/photoswipe/dist/default-skin/default-skin.css',
            $dir . '/assets/bower_components/justifiedGallery/dist/css/justifiedGallery.css',
            ));

        $compiler = WebLoader\Compiler::createCssCompiler($files, $dir . '/webtemp');

        $compiler->addFilter(function ($code) {
            return cssmin::minify($code);
        });

        $control = new WebLoader\Nette\CssLoader($compiler, $this->template->basePath. '/webtemp');
        $control->setMedia('screen');

        return $control;
    }
    
     protected function createComponentJs() {
        $dir = $this->webDir->getPath('');
        $files = new WebLoader\FileCollection($dir);
        $files->addFiles(array(
            $dir . '/assets/bower_components/jquery/dist/jquery.min.js',
            $dir . '/assets/bower_components/bootstrap/dist/js/bootstrap.min.js',
            $dir . '/assets/bower_components/video.js/dist/video.min.js',
            $dir . '/assets/js/jquery.bootstrap-autohidingnavbar.min.js',
            $dir . '/assets/bower_components/photoswipe/dist/photoswipe.min.js',
            $dir . '/assets/bower_components/photoswipe/dist/photoswipe-ui-default.min.js',
            $dir . '/assets/bower_components/justifiedGallery/dist/js/jquery.justifiedGallery.min.js',
            $dir . '/assets/bower_components/masonry/dist/masonry.pkgd.min.js',
            $dir . '/assets/js/x-tag-components.js',
            $dir . '/assets/js/jquery.tagcloud.js/jquery.tagcloud.js',
            $dir . '/assets/js/utils.js'
            ));

        $compiler = WebLoader\Compiler::createJsCompiler($files, $dir . '/webtemp');

        $control = new WebLoader\Nette\JavaScriptLoader($compiler, $this->template->basePath. '/webtemp');

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
