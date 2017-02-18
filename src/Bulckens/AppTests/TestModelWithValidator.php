<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;

class TestModelWithValidator extends Model {

  // Fillable and visible attributes
  protected $fillable = [ 'name' ];

  public function rules() {
    return [
      'name' => [ 'required' => true ]
    ];
  }

}
