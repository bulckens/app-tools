<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;
use Bulckens\AppTools\Traits\Cacheable;

class TestModelWithCacheable extends Model {

  use Cacheable;

  public $id;

  public function cacheId() {
    return $this->id;
  }

}
