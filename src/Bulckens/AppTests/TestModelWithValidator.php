<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;

class TestModelWithValidator extends Model {

  // Fillable and visible attributes
  protected $fillable = [ 'name' ];
  protected $table = 'test_models';

  public function rules() {
    return [
      'name' => [ 'required' => true ]
    ];
  }

}
