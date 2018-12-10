<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;
use Bulckens\AppTools\Traits\Diggable;

class TestModelWithCustomDiggable extends Model {

  use Diggable;

  protected $diggable_key = 'data';

  public function __construct() {
    $this->data = [
      'apparatus' => 'Flying Spaghetti Monster'
    , 'first' => [
        'apparatus' => 'pastafari'
      , 'second' => [
          'apparatus' => 'pastover'
        ]
      ]
    ];
  }

}