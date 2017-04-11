<?php

namespace Bulckens\AppTools;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Bulckens\AppTools\Validator\Validation;

abstract class Model extends Eloquent {

  use Validation;

  // Make sure model is valid before saving
  public function save( array $options = [] ) {
    if ( $this->isValid() )
      return parent::save();

    return false;
  }
  
}