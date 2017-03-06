<?php

namespace Bulckens\AppTools\View;

use Twig_Extension;
use Twig_SimpleFunction;
use Bulckens\Helpers\UrlHelper;

class UrlExtension extends Twig_Extension {

  // Get twig functions
  public function getFunctions() {
    return [

      new Twig_SimpleFunction( 'current_path', function( $path = null ) {
        return UrlHelper::currentPath( $path );
      })

    , new Twig_SimpleFunction( 'root_path', function( $path = null ) {
        return UrlHelper::rootPath( $path );
      })

    , new Twig_SimpleFunction( 'ssl_enabled', function() {
        return UrlHelper::ssl();
      })

    ];
  }

}
