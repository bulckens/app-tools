<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;
use Bulckens\AppTools\Traits\Diggable;

class TestModelWithDiggable extends Model {

  use Diggable;

  public function __construct() {
    $this->diggable = [
      'apparatus' => 'time travel machine'
    , 'first' => [
        'apparatus' => 'fridge'
      , 'second' => [
          'apparatus' => 'felange'
        ]
      ]
    ];
  }

}
