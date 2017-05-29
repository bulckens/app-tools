<?php

namespace Bulckens\AppTools\Validator;

use Bulckens\AppTools\Validator;

trait Validation {

  protected $rules;
  protected $errors = [];

  // Validate data before saving
  public function isValid() {
    // get rules
    $rules = $this->rules() ?: $this->rules;
    
    if ( $rules ) {
      // initialize validator
      $validation = new Validator( $rules );
      $validation->data( $this->getAttributes() );
      $validation->model( $this );
      
      // check for failure
      if ( $validation->fails() ) {
        // set errors and return false
        $this->errors = $validation->errors();

        return false;
      }

      // validate associations
      if ( isset( $this->associations ) && is_array( $this->associations ) ) {
        foreach ( $this->associations as $models ) {
          foreach ( $models as $model ) {
            if ( ! $model->isValid() ) {
              // add association errors
              $this->errors = array_replace( $this->errors, $model->errors() );

              return false;
            }
          }
        }
      }
    }

    // validation passes
    return true;
  }

  // Perform negative validation
  public function isInvalid() {
    return ! $this->isValid();
  }

  // Get errors generated by validator
  public function errors() {
    return $this->errors;
  }

  // Get errors on a given attribute
  public function errorsOn( $attr ) {
    if ( ! isset( $this->errors[$attr] ) )
      return false;

    return $this->errors[$attr];
  }

  // Protect rules
  protected function rules() {}

}