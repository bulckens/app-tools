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

  // Initialize twig
  public function __construct() {
    if ( ! $this->config( 'root' ) )
      throw new ViewRootNotDefinedException( "View root is not defined in {$this->file()}" );

    // initialize render environments
    $loader = new Twig_Loader_Filesystem([ App::root( $this->config( 'root' ) )]);
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


  // Render a given view
  public function render( $view, $locals = [] ) {
    // add current env locals
    $locals['app'] = App::toArray();

    // detect file name
    if ( preg_match( '/^[a-z0-9\/\.\-\_]+\.(twig|html)$/', $view ) )
      return $this->view->render( $view, $locals ); 
    else
      return $this->text->render( $view, $locals );
  }

}

// Exceptions
class ViewRootNotDefinedException extends Exception {};