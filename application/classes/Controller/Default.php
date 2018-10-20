<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * Kontroler odpowiedzialny za przygotowanie strony HTML
 */
class Controller_Default extends Controller_Template
{
    /**
     * Domyslny szablon
     * 
     * @var string
     */
    public $template = 'default';
    
    /**
     * Style startowe
     * @var array
     */
    protected $_styles = array(
        'media/css/bootstrap.css' => 'screen',
    );
    
    /**
     * Pliki javascript
     * @var array
     */
    protected $_scripts = array(
        'media/js/bootstrap.js',
    );
    
    /**
     * Tablica plikow ze stalymi jezykowymi
     * 
     * @var array 
     */
    protected $_langFiles = array();
    
    /**
     * Tablica javascriptow bezposrednio wlaczanych w strone
     * 
     * @var array 
     */
    
    protected $_inlineScripts = array();

    /**
     * @var \JQuery_Inline
     */
    protected $JSInline;

    /**
     * Konstruktor
     * 
     * @param Request $request
     * @param Response $response 
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        if (isset($_COOKIE['lang']))
        {
            setcookie("lang", $_COOKIE['lang'], 31536000 + time(), '/');
            I18n::lang($_COOKIE['lang']);
        }
    }

    /**
     * Metoda wykonywana przed ladowaniem strony
     */
    
    public function before()
    {
        parent::before();

        if ($this->auto_render)
        {
            // Initialize empty values
            $this->template->title = '';
            $this->template->description = '';
            $this->template->keywords = '';
            $this->template->content = '';
            $this->template->aView = array();
            $this->template->View = null;
        }
    }

    /**
     * Metoda wykonywana po wygenerowaniu strony
     */
    public function after()
    {
        if ($this->auto_render)
        {
            $this->template->_styles  = $this->getStyles();
            $this->template->_scripts = $this->getScripts();
            $this->template->_inlineScripts = $this->getInlineScripts();
            $this->template->langs = Kohana::$config->load('lang');
            
        }

        parent::after();
    }

    /**
     * Strona glowna
     */
    
    public function action_index()
    {
        $aView = array();
        
        $jsVersion = '1212';
        $this->template->content = View::factory('index')
                ->set('jsVersion', $jsVersion)
                ->bind('aView', $aView);
        $this->template->title = __('index.title');
        $this->template->aView = $aView;        
    }
    
    /**
     * Dodaj plik CSS
     * 
     * @param string $path
     * @param string $type 
     */

    public function addStyle($path, $type = 'screen')
    {
        $this->_styles[$path] = $type;
    }

    /**
     * Pobierz tablice plikow CSS
     * 
     * @return array 
     */
    public function getStyles()
    {
        return $this->_styles;
    }

    /**
     * Dodaj plik js
     * 
     * @param string $path 
     */
    public function addScript($path)
    {
        $this->_scripts[] = $path;
    }
    
    /**
     * Dodaj plik js wywolywany bezposrednio na stronie
     * 
     * @param type $path 
     */
    public function addInlineScript($path)
    {
        $this->_inlineScripts[] = $path;
    }

    /**
     * Tablica plikow javascript
     * 
     * @return array
     */
    public function getScripts()
    {
        return $this->_scripts;
    }
    
    /**
     * Tablica skryptow wlaczanych bezposrednio w tresc strony
     * @return type
     */
    public function getInlineScripts()
    {
        return $this->_inlineScripts;
    }
    
    /**
     * Dodanie pliku jezykowego
     * 
     * @param string $file
     */
    public function addLangFile($file)
    {
        $this->_langFiles[] = $file;
    }
    
    /**
     * Brak strony: HTTP 401
     */
    public function action_403()
    {
        $this->response->status(401);

        $this->template->content = View::factory('error/401');
//        if ($this->request->is_ajax())
//        {
////            $this->template->content = 'Błąd autoryzacji';
//        }
//        else
//        {
//        }
    }
    
    /**
     * Brak strony: HTTP 401
     */
    public function action_401()
    {
        $this->response->status(401);

        if ($this->request->is_ajax())
        {
            $this->template->content = 'Błąd autoryzacji';
        }
        else
        {
            $this->template->content = View::factory('error/401');
        }
    }
}
