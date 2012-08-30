<?php

/**
 * Template Class
 * @package    Template
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @version    1.0.20120804
 * @copyright  Copyright (c) 2012, Giuseppe Di Terlizzi
 * @link       https://github.com/LotarProject/Template/
 * @license    GNU GPLv2 (see LICENSE.txt)
 */

class Template {


  const VERSION = 1.0;
  const BUILD   = 20120804;
  

  protected $vars = array();


  /**
   * Directory di default per l'inclusione del template
   * @var string
   */
  static $DIRECTORY = '';

  /**
   * Template di wrapper
   * @var string
   */
  static $WRAPPER = null;


  /**
   * Template Constructor
   * @param  array   $options    Opzioni di configurazione
   */
  public function __construct($options = array()) {

    if (is_string($options)) {

      $directory = $options;
      unset($options);
      $options['DIRECTORY'] = $directory;

    }

    $this->applyConfig($options);

  }


  /**
   * Applica le configurazioni passate come argomento al costruttore
   * @access private
   * @param  array   $options  Opzioni del costruttore
   */
  private function applyConfig($options = array()) {

    foreach ($options as $key => $value) {

      switch ($key) {

        case 'DIRECTORY':
          if ( ! preg_match('/\/$/', $value) ) {
            $value .= '/';
          }
          self::${$key} = $value;
          break;

        case 'VARIABLES':
          $this->setVars($value);
          break;

        default:
          self::${$key} = $value;
      }

    }

  }


  /**
   * Processa in template includendolo nello script
   * 
   * <code>
   *
   *  File page.php :
   *
   *  <?php
   *  
   *    $Template = new Template("__DIR__/templates/");
   *
   *    $vars = array(
   *      'TITLE'   => 'Lorem Ipsum',
   *      'CONTENT' => 'Lorem ipsum dolor sit amet, [...]',
   *    );
   *
   *    $Template->process('layout.php', $vars);
   *    
   *  ?>
   *
   *  File layout.php :
   *
   *  <html>
   *  <head>
   *    <title><?php echo $TITLE; ?></title>
   *  </head>
   *  <body>
   *    <h1>
   *      <?php echo $TITLE; ?>
   *    </h1>
   *    <p>
   *      <?php echo $CONTENT; ?>
   *    </p>
   *  </body>
   *  </html>
   *
   *  Output of page.php :
   *
   *  <html>
   *  <head>
   *    <title>Lorem Ipsum</title>
   *  </head>
   *  <body>
   *    <h1>
   *      Lorem Ipsum
   *    </h1>
   *    <p>
   *      Lorem ipsum dolor sit amet, [...]
   *    </p>
   *  </body>
   *  </html>
   *  
   * </code>
   * 
   * @param   string    $template
   * @param   array     $vars
   */
  public function process($template, $vars = array()) {

    if (self::$WRAPPER) {
      $this->wrapper(self::$WRAPPER, $template, $vars);
    }
    else {
      $this->includeTemplate($template, $vars);
    }

  }


  /**
   * Include il template esportando le variabili contenute nell'array
   * @access  private
   * @param   string  Template
   * @param   array   Variabili da esportare
   */
  private function includeTemplate($template, $vars) {

    $file = self::$DIRECTORY . $template;
  
    if (file_exists($file)) {

      $this->setVars($vars);
      extract($this->getVars(), true);

      include($file);

    }
    else {
      throw new Exception("Template file '$file' was not found", 404);
    }

  }


  /**
   * Processa un template e restituendo una stringa
   * @param   string    $template
   * @return  string
   */
  public function toString($template, $vars = array()) {
  
    ob_start();
    $this->process($template, $vars);
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }


  /**
   * Inserisce le variabili (o un template già elaborato) dentro un template che
   * abbia un "placeholder" richiamabile con "$this->CONTENT", "$CONTENT" oppure
   * "$this->getContent()".
   * 
   * <code>
   * 
   * File: foo.php
   *
   * <?php
   *   echo "Foo, $CONTENT!";
   * ?>
   *
   *
   * File: bar.php
   *
   * <?php
   *   echo "Bar, $MY_VAR";
   * ?>
   *
   *
   * File: page.php
   *
   * <?php
   * 
   *    $Template = new Template("__DIR__/templates/");
   *
   *    // Example 1: Include il contenuto del template "bar.php" processato con
   *    //            la variabile "$MY_VAR" in "$CONTENT" presente nel template
   *    //            "foo.php".
   *    $Template->wrapper('foo.php', 'bar.php', array('MY_VAR' => 'Baz'));
   *
   *    // Output:
   *    // Foo, Bar, Baz!
   *
   *
   *    // Example 2: Include in "$CONTENT" nello script "foo.php" il template
   *    //             "bar.php" già processato insieme alla variabile "$MY_VAR"
   *    $Template->wrapper('foo.php', $Template->toString('bar.php', array('MY_VAR' => 'Baz')));
   *
   *    // Output:
   *    // Foo, Bar, Baz!
   *
   *
   *    // Example 3: Aggiunge la stringa "Baz" alla variabile "$CONTENT" del
   *    //            template "foo.php"
   *    $Template->wrapper('foo.php', 'Baz');
   *
   *    // Output:
   *    // Foo, Baz!
   *
   * ?>
   * 
   * </code>
   * 
   * @see Template::process()
   * @see Template::getContent()
   * @param   string  $wrapper    Nome del template wrapper
   * @param   mixed   $template   Variabili in array o stringa
   *                                oppure un template già elaborato con Template::toString()
   *                                oppure un template da includere
   * @param   array   $vars       Array di variabili
   */
  public function wrapper($wrapper, $template, $vars = null) {

    if (self::$WRAPPER) {
      $_tmp_wrapper = self::$WRAPPER;
      self::$WRAPPER = null;
    }

    if ($template && ! $vars) {

      $vars = $template;

      $this->vars['CONTENT'] = $vars;
      $this->process($wrapper);

    }
    elseif ($template && $vars) {
      $this->vars['CONTENT'] = $this->toString($template, $vars);
      $this->process($wrapper);
    }

    if (isset($_tmp_wrapper)) {
      self::$WRAPPER = $_tmp_wrapper;
    }

  }

  
  /**
   * Recupera le variabili
   * @return  array
   */
  public function getVars() {
    return $this->vars;
  }


  /**
   * Cancella tutte le variabili
   */
  public function clearVars() {
    $this->vars = array();
  }


  /**
   * Imposta o effettua il merge delle variabili nel pool di dati
   * @param  array
   */
  private function setVars($vars = array()) {

    if ($this->vars) {
      if (count($vars) == 0) {
        $vars = $this->vars;
      }
      else {
        $old_vars = $vars;
        $vars = array_merge($this->vars, $old_vars);
      }
    }

    foreach ($vars as $key => $value) {
      $this->vars[$key] = $value;
    }

  }


  public function getContent($to_string = false) {

    if ( ! $to_string ) {
      return @$this->vars['CONTENT'];
    }

    echo @$this->vars['CONTENT'];

  }


  public function __set($key, $value) {
    $this->vars[$key] = $value;
  }


  public function __get($key) {

    if (array_key_exists($key, $this->vars)) {
      return $this->vars[$key];
    }
    else {
      return null;
    }

  }


  public static function toClass($class) {

    if (! is_array($class)) {
      return '';
    }

    return implode(' ', $class);

  }
  
  
  public static function toInlineStyle($style) {

    if (! is_array($style)) {
      return '';
    }
  
    $_style = '';

    foreach ($style as $attribute => $value) {
      if ($value) {
        $_style .= "$attribute:$value;";
      }
    }
    
    return $_style;

  }


  public function addStyle($style, $order = null) {

    if ($order) {
      $this->vars['STYLE'][$order] = $style;
    }
    else {
      $count = @count($this->vars['STYLE']);
      $this->vars['STYLE'][$count] = $style;
    }

  }


  public function getStyle() {

    if (array_key_exists('STYLE', $this->vars)) {
      ksort($this->vars['STYLE']);
      return $this->vars['STYLE'];
    }

    return null;

  }

  
  public function addScript($script, $order = null) {

    if ($order) {
      $this->vars['SCRIPT'][$order] = $script;
    }
    else {
      $count = @count($this->vars['SCRIPT']);
      $this->vars['SCRIPT'][$count] = $script;
    }

  }


  public function getScript() {

    if (array_key_exists('SCRIPT', $this->vars)) {
      ksort($this->vars['SCRIPT']);
      return $this->vars['SCRIPT'];
    }

    return null;

  }


  public function setTitle($title) {
    $this->vars['TITLE'] = $title;
  }

  
  public function getTitle() {

    if (array_key_exists('TITLE', $this->vars)) {
      return $this->vars['TITLE'];
    }
    
    return null;

  }  
  
}
