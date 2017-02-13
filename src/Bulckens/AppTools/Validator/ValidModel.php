<?php

namespace Bulckens\AppTools\Validator;

use Illuminate\Database\Eloquent\Model;

abstract class ValidModel extends Model {

  use Validation;

  // Make sure model is valid before saving
  public function save( array $options = [] ) {
    if ( $this->isValid() )
      return parent::save();

    return false;
  }

}