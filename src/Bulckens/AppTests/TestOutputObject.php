<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Interfaces\OutputInterface;

class TestOutputObject implements OutputInterface {

  public function render( $format ) {
    switch ( $format ) {
      case 'too':
        return '[too]Welcome to lonelyness[/too]';
      break;
    }
  }

  public function toArray() {
    return [
      'too' => 'Welcome to lonelyness'
    ];
  }

}
