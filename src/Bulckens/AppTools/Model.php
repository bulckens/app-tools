<?php

namespace Bulckens\AppTools;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Bulckens\AppTools\Traits\Uploadable;
use Bulckens\AppTools\Traits\Validatable;
use Bulckens\AppTools\Traits\NestedAssociations;

abstract class Model extends Eloquent {

  use Uploadable;
  use Validatable;
  use NestedAssociations;

  // Make sure model is valid before saving
  public function save( array $options = [] ) {
    if ( $this->isValid() ) {
      if ( $save = parent::save( $options ) ) {
        $this->saveNestedAssociations();
      }
      
      return $save;
    }

    return false;
  }
  
}