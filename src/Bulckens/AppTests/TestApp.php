<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\App;

class TestApp extends App {

  protected $customized = false;

  // Prepare app
  public function customize() {
    $this->customized = true;
    return $this;
  }

  // Check if customized
  public function customized() {
    return $this->customized;
  }

  // Parent app render method
  public static function render( $output ) {
    switch ( $output->format() ) {
      case 'html':
        return '<html><head><title></title></head><body>Rendered from the outside!</body></html>';
      break;
    }
  }

}
