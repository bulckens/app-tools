<?php

namespace Bulckens\AppTools;

class View {

  // protected static $twig;

  // // Initialize twig
  // public function __construct() {
  //   // initialize loader
  //   $loader = new Twig_Loader_Filesystem( [ App::root( 'app/Stencils/Views' ) ] );

  //   // initialize twig
  //   if ( App::env( 'development' ) ) {
  //     self::$twig = new Twig_Environment( $loader, [
  //       'debug' => true
  //     ]);

  //     // enable debug mode
  //     self::$twig->addExtension( new Twig_Extension_Debug() );
  //   } else {
  //     self::$twig = new Twig_Environment( $loader );
  //   }

  //   // Add extensions
  //   self::$twig->addExtension( new Twig_Extensions_Extension_Text() );
  // }
  
  // // Get twig instance
  // public static function get() {
  //   return self::$twig;
  // }

  // // Render a given view
  // public static function render( $view, $locals = [] ) {
  //   // add current env locals
  //   $locals['env'] = App::env();

  //   return self::$twig->render( $view, $locals );
  // }

}