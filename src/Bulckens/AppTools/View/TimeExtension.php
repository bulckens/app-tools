<?php

namespace Bulckens\AppTools\View;

use Twig_Extension;
use Twig_SimpleFunction;
use Bulckens\Helpers\TimeHelper;

class TimeExtension extends Twig_Extension {

  // Get twig functions
  public function getFunctions() {
    return [

      new Twig_SimpleFunction( 'milliseconds', function() {
        return TimeHelper::milliseconds();
      })

    ];
  }

}
