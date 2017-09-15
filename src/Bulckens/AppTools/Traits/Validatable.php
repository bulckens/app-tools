<?php

namespace Bulckens\AppTools\Traits;

use Bulckens\AppTools\Validator;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Validatable {

  protected $rules;
  protected $errors = [];

  // Validate data before saving
  public function isValid() {
    // get rules
    $rules = $this->rules() ?: $this->rules;
    $valid = true;
    
    if ( $rules ) {
      // initialize validator
      $validation = new Validator( $rules );
      $validation->data( $this->getAttributes() );
      $validation->model( $this );
      
      // check for failure
      if ( $validation->fails() ) {
        // set errors and return false
        $this->errors = $validation->errors();

        $valid = false;
      }

      // validate associations
      if ( isset( $this->associations ) && is_array( $this->associations ) ) {
        // initialize error storage for associations
        $this->errors['associations'] = [];

        // validate every given list of associations
        foreach ( $this->associations as $name => $relation ) {
          // get relation type
          $type = $this->$name();

          // initialize error storage for relation
          $this->errors['associations'][$name] = [];

          // detect relation type
          if ( $type instanceof HasOne ) {
            if ( $relation->isInvalid() ) {
              // add association model errors
              $this->errors['associations'][$name] = $relation->errors();
              
              $valid = false;
            }

          } elseif ( $type instanceof HasMany ) {
            for ( $i = 0; $i < count( $relation ); $i++ ) {
              // initialize error store for instance at given position
              $this->errors['associations'][$name][$i] = [];

              if ( $relation[$i]->isInvalid() ) {
                // add association model errors
                $this->errors['associations'][$name][$i] = $relation[$i]->errors();
                
                $valid = false;
              }
            }

          }
        }
      }
    }

    // validation passes
    return $valid;
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