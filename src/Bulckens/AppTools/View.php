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
use Bulckens\AppTraits\Configurable;

class View {

  use Configurable;

  protected $view;

  // Initialize twig
  public function __construct() {
    if ( $this->config( 'root' ) ) {
      // initialize loader
      $loader = new Twig_Loader_Filesystem([ App::root( $this->config( 'root' ) )]);

      // initialize twig
      if ( $this->config( 'debug' ) ) {
        $this->view = new Twig_Environment( $loader, [
          'debug' => true
        ]);

        // enable debug mode
        $this->view->addExtension( new Twig_Extension_Debug() );
      } else {
        $this->view = new Twig_Environment( $loader );
      }

      // Add extensions
      $this->view->addExtension( new Twig_Extensions_Extension_Text() );
      $this->view->addExtension( new Twig_Extension_Escaper( 'html' ) );

    } else {
      throw new ViewRootNotDefinedException( "View root is not defined in {$this->file()}" );
    }
  }


  // Add filters
  public function filters( $filters ) {
    $options = [ 'is_safe' => [ 'html' ] ];
    
    foreach ( $filters as $name => $function )
      $this->view->addFilter( new Twig_SimpleFilter( $name, $function, $options ) );
  }


  // Add functions
  public function functions( $functions ) {
    $options = [ 'is_safe' => [ 'html' ] ];

    foreach ( $functions as $name => $function )
      $this->view->addFunction( new Twig_SimpleFunction( $name, $function, $options ) );
  }


  // Render a given view
  public function render( $view, $locals = [] ) {
    // add current env locals
    $locals['app'] = App::toArray();

    return $this->view->render( $view, $locals );
  }

}

// Exceptions
class ViewRootNotDefinedException extends Exception {};