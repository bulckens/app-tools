<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;

class TestModel extends Model {

  public function parent() {
    return $this->belongsTo( 'Bulckens\AppTests\TestModel' );
  }

  public function children() {
    return $this->hasMany( 'Bulckens\AppTests\TestModel', 'parent_id' );
  }

}
