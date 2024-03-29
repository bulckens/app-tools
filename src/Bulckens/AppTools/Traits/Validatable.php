<?php

namespace Bulckens\AppTools\Traits;

use Bulckens\AppTools\Validator;
use Bulckens\AppTools\Helpers\NestedAssociationsHelper;

trait Validatable {

  protected $rules;
  protected $errors = [];

  // Validate data before saving
  public function isValid() {
    // get rules
    $rules = $this->rules() ?: $this->rules;
    
    if ( $rules ) {
      // get attributes
      $attributes = $this->getAttributes();

      if ( isset( $this->upload_queue ) && is_array( $this->upload_queue ) ) {
        $attributes = array_replace( $attributes, $this->upload_queue );
      }

      // initialize validator
      $validation = new Validator( $rules );
      $validation->data( $attributes );
      $validation->model( $this );
      
      // check for failure
      if ( $validation->fails() ) {
        // set errors and return false
        $this->errors = $validation->errors();
      }

      // validate associations
      if ( isset( $this->associations ) && is_array( $this->associations ) ) {
        // validate every given list of associations
        foreach ( $this->associations as $name => $instance ) {
          // get relation type
          $type = $this->$name();

          // detect relation type
          if ( NestedAssociationsHelper::hasOne( $type ) ) {
            if ( $instance->isInvalid() ) {
              // add association model errors
              $this->errors[$name] = $instance->errors();
            }

          } elseif ( NestedAssociationsHelper::hasMany( $type ) ) {
            for ( $i = 0; $i < count( $instance ); $i++ ) {
              if ( $instance[$i]->isInvalid() ) {
                // add association index to errors
                $errors = $instance[$i]->errors();
                $errors['_index'] = $i;

                // make sure array exists
                if ( ! isset( $this->errors[$name] ) ) {
                  $this->errors[$name] = [];
                }

                // add association model errors
                array_push( $this->errors[$name], $errors );
              }
            }
          }
        }
      }
    }

    // validation passes
    return empty( $this->errors );
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