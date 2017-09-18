<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;

class TestModelWithValidator extends Model {

  protected $table = 'test_models';

  // Fillable and visible attributes
  protected $fillable = [ 'name', 'nested_associations' ];
  protected $nested_associations = [
    'children' => true
  ];

  public function rules() {
    return [
      'name' => [ 'required' => true ]
    ];
  }

  // Children relation
  public function children() {
    return $this->hasMany( 'Bulckens\AppTests\TestModelWithValidator', 'parent_id' );
  }

}
