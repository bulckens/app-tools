<?php

namespace Bulckens\AppTools\View;

use Twig_Extension;
use Twig_SimpleFilter;
use Illuminate\Support\Str;

class StringExtension extends Twig_Extension {

  // Get twig filters
  public function getFilters() {
    return [

      new Twig_SimpleFilter( 'singular', function( $string ) {
        return Str::singular( $string );
      })

    , new Twig_SimpleFilter( 'plural', function( $string ) {
        return Str::plural( $string );
      })

    , new Twig_SimpleFilter( 'pluralize', function( $string, $count ) {
        return $count == 1 ? Str::singular( $string ) : Str::plural( $string );
      })

    ];
  }

}