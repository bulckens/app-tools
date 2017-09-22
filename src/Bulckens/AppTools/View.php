<?php

namespace Bulckens\AppTools;

use Exception;
use Twig_Loader_Filesystem;
use Twig_Extension_Debug;
use Twig_Environment;
use Twig_Loader_String;
use Twig_Extension_Escaper;
use Twig_Extensions_Extension_Text;
use Twig_SimpleFunction;
use Twig_SimpleFilter;
use Bulckens\AppTools\Traits\Configurable;

class View {

  use Configurable;

  protected $view;
  protected $text;
  protected $root;


  public function __construct( $run = true ) {
    if ( $run ) $this->run();
  }


  // Activate view engine
  public function run() {
    // make sure the root is defined
    if ( ! $this->config( 'root' ) && is_null( $this->root ) )
      throw new ViewRootNotDefinedException( "View root is not defined in {$this->configFile()} nor is there a custom root" );

    // make sure the root exists
    $root = $this->root ? $this->root : App::root( $this->config( 'root' ) );

    if ( ! file_exists( $root ) )
      throw new ViewRootMissingException( "The given view root $this->root does not exist" );      

    // initialize render environments
    $loader = new Twig_Loader_Filesystem([ $root ]);
    $string = new Twig_Loader_String();

    // initialize twig 
    if ( $this->config( 'debug' ) ) {
      // initialize debug extension
      $debug   = new Twig_Extension_Debug();
      $options = [ 'debug' => true ];

      // renderers
      $this->view = new Twig_Environment( $loader, $options );
      $this->text = new Twig_Environment( $string, $options );

      // enable debug mode
      $this->view->addExtension( $debug );
      $this->text->addExtension( $debug );
    } else {
      $this->view = new Twig_Environment( $loader );
      $this->text = new Twig_Environment( $string );
    }

    // Add extensions
    $this->view->addExtension( new Twig_Extensions_Extension_Text() );
    $this->view->addExtension( new Twig_Extension_Escaper( 'html' ) );

    return $this;
  }


  // Add filters
  public function filters( $filters ) {
    $options = [ 'is_safe' => [ 'html' ] ];
    
    foreach ( $filters as $name => $function ) {
      $f = new Twig_SimpleFilter( $name, $function, $options );
      $this->view->addFilter( $f );
      $this->text->addFilter( $f );
    }
  }


  // Add functions
  public function functions( $functions ) {
    $options = [ 'is_safe' => [ 'html' ] ];

    foreach ( $functions as $name => $function ) {
      $f = new Twig_SimpleFunction( $name, $function, $options );
      $this->view->addFunction( $f );
      $this->text->addFunction( $f );
    }
  }


  // Add extension
  public function extension( $extension ) {
    $this->view->addExtension( $extension );
    $this->text->addExtension( $extension );
  }


  // Render a given view
  public function render( $view, $locals = [] ) {
    // make sure view engine is running
    if ( is_null( $this->view ) )
      $this->run();

    // add current env locals
    $locals['app'] = App::toArray();

    // detect file name
    if ( preg_match( '/^[a-z0-9\/\.\-\_]+\.(twig|html)$/', $view ) )
      return $this->view->render( $view, $locals ); 
    else
      return $this->text->render( $view, $locals );
  }


  // Set custom root
  public function root( $root = null ) {
    // act as getter
    if ( is_null( $root ) )
      return $this->root;

    // act as setter
    $this->root = $root;

    return $this;
  }


  // Reset stored variables
  public function reset() {
    unset( $this->view );
    unset( $this->text );
    $this->root = null;

    return $this;
  }


  // Returns the render engine
  public function engine() {
    return $this->view;
  }

}

// Exceptions
class ViewRootNotDefinedException extends Exception {};
class ViewRootMissingException extends Exception {};