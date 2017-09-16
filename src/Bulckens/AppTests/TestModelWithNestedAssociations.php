<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;

class TestModelWithNestedAssociations extends Model {

  protected $table = 'test_models';

  // Fillable and visible attributes
  protected $fillable = [ 'name', 'nested_associations' ];
  protected $nested_associations = [ 'children', 'sibling', 'friends' ];


  public function rules() {
    return [
      'name' => [ 'required' => true ]
    ];
  }


  // Children relation
  public function children() {
    return $this->hasMany( 'Bulckens\AppTests\TestModelWithValidator', 'parent_id' );
  }


  // Sibling relation
  public function sibling() {
    return $this->hasOne( 'Bulckens\AppTests\TestModelWithValidator', 'parent_id' );
  }


  // Polymorphic gateway
  public function friendable() {
    return $this->morphTo();
  }


  // Polymorphic relation to many
  public function friends() {
    return $this->morphMany( 'Bulckens\AppTests\TestModelWithNestedAssociations', 'friendable' );
  }


  // Polymorphic relation to one
  public function friend() {
    return $this->morphOne( 'Bulckens\AppTests\TestModelWithNestedAssociations', 'friendable' );
  }


}