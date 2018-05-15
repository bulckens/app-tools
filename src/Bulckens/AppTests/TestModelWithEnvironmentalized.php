<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;
use Bulckens\AppTools\Traits\Configurable;
use Bulckens\AppTools\Traits\Environmentalized;

class TestModelWithEnvironmentalized extends Model {

  use Configurable;
  use Environmentalized;

  public function __construct( $env = null ) {
    self::$env = $env;
  }

}