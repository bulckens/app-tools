<?php

namespace Bulckens\AppTools;

class Validator {
  
  protected $data = [];
  protected $rules;
  protected $model;
  protected $class;
  protected $exists;
  protected $errors   = [];
  protected $messages = [];
  protected $defaults = [
    // base values to fall back on
    'base' => [
      'required'            => 'is required'
    , 'forbidden'           => 'is not allowed'
    , 'min'                 => 'should be longer than {{ length }} characters'
    , 'max'                 => 'should be shorter than {{ length }} characters'
    , 'match'               => 'does not have the right format'
    , 'match_email'         => 'is not an email address'
    , 'match_email_address' => 'does not contain an email address'
    , 'match_alpha'         => 'should be letters-only and all lowercase'
    , 'match_ALPHA'         => 'should be letters-only and all uppercase'
    , 'match_Alpha'         => 'should be letters-only'
    , 'match_alphanumeric'  => 'should be alphanumeric and all lowercase'
    , 'match_ALPHANUMERIC'  => 'should be alphanumeric and all uppercase'
    , 'match_Alphanumeric'  => 'should be alphanumeric'
    , 'confirmation'        => 'confirmation does not match'
    , 'exactly'             => 'is not the expected value'
    , 'in'                  => 'could not be found in the list'
    , 'not_in'              => 'should not be found in the list'
    , 'numeric'             => 'is not the right numeric value'
    , 'numeric_even'        => 'is not a an even number'
    , 'numeric_odd'         => 'is not a an odd number'
    , 'numeric_integer'     => 'is not a an integer'
    , 'unique'              => 'is already taken'
    , 'custom'              => 'is invalid'
    ]
  ];


  // Initialization
  public function __construct( $rules ) {
    // store rules
    $this->rules = $rules;
  }


  // Store data to test against
  public function data( $data = null ) {
    if ( is_null( $data ) )
      return $this->data;

    $this->data = $data;

    return $this;
  }


  // Store object and its status
  public function model( $model = null, $id = null ) {
    // act as getter
    if ( is_null( $model ) )
      return $this->model;

    // store class
    $this->class = is_string( $model ) ? $model : get_class( $model );

    // load record
    if ( is_string( $model ) && $id && ( $record = $model::find( $id ) ) )
      $model = $record;

    // store model
    if ( ! is_string( $model ) ) {
      // store model as it is
      $this->model = $model;
    }

    // add convenience tester to test model existance
    $this->exists = ! empty( $this->model->id );

    return $this;
  }


  // Store custom messages
  public function messages( $messages ) {
    $this->messages = array_replace_recursive( $this->messages, $messages );

    return $this;
  }


  // Perform validation
  public function passes() {
    // rules to execute even if no data is present
    $force = [ 'required', 'custom' ];

    // clear errors array
    $this->errors = [];

    // run through all given rules
    foreach ( $this->rules as $name => $value ) {
      // run every rule
      foreach ( $value as $rule => $constraint ) {
        // perform test
        if ( in_array( $rule, $force ) || isset( $this->data[$name] ) )
          $this->{$rule}( $name, $constraint );
      }
    }

    return empty( $this->errors() );
  }


  // Perform negative validation
  public function fails() {
    return ! $this->passes();
  }


  // Return errors variable
  public function errors( $key = null ) {
    if ( is_null( $key ) )
      return $this->errors;
    
    if ( isset( $this->errors[$key] ) )
      return $this->errors[$key];
  }


  // Return error messages
  public function errorMessages( $key = null ) {
    if ( ! is_null( $key ) ) {
      if ( isset( $this->errors[$key] ) )
        return array_values( $this->errors[$key] );
    }

    $messages = [];

    // flatten array
    foreach ( $this->errors as $key => $errors )
      $messages[$key] = array_values( $errors );
    
    return $messages;
  }


  // Add an error
  protected function error( $name, $key, $values = [] ) {
    // initialzie message
    $message = null;

    // build full values array
    $values['name'] = $name;
    $values['key']  = $key;

    // filter out message
    if ( isset( $values['message'] ) ) {
      // get message from values
      $message = $values['message'];

      // delete message in values
      unset( $values['message'] );
    }

    // get it from the user-isset specific messages
    if ( ! $message && isset( $this->messages['specific'] ) ) {
      if ( isset( $this->messages['specific'][$name] ) ) {
        if ( isset( $this->messages['specific'][$name][$key] ) ) {
          $message = $this->messages['specific'][$name][$key];
        }
      }
    }

    // get it from the user-isset base messages
    if ( ! $message && isset( $this->messages['base'] ) ) {
      if ( isset( $this->messages['base'][$key] ) ) {
        $message = $this->messages['base'][$key];
      }
    }

    // get message from defaults
    if ( ! $message )
      $message = $this->defaults['base'][$key];
    
    // ensure error array
    if ( ! isset( $this->errors[$name] ) )
      $this->errors[$name] = [];

    // add error
    $this->errors[$name][$key] = $this->interpolate( $message, $values );
  }


  // Validate presence
  protected function required( $name, $constraint ) {
    // make constraint dependent
    $required = $constraint;

    if ( $constraint == 'create' )
      $required = ! $this->exists;
    else if ( $constraint == 'update' )
      $required = $this->exists;

    // make sure value is restored to original if not present
    if ( ! isset( $this->data[$name] ) ||  $this->isBlank( $this->data[$name] ) ) {
      if ( $required ) {
        $this->error( $name, 'required' );
      } else if ( $this->model ) {
        $original = $this->model->getOriginal();
        $this->model->$name = $original[$name];
      }
    }
  }


  // Validate non-presence
  protected function forbidden( $name ) {
    if ( ! $this->isBlank( $this->data[$name] ) )
      $this->error( $name, 'forbidden' );
  }


  // Validate min length
  protected function min( $name, $length ) {
    if ( ! empty( $this->data[$name] ) && strlen( $this->data[$name] ) < $length )
      $this->error( $name, 'min', [ 'length' => $length ] );
  }


  // Validate max length
  protected function max( $name, $length ) {
    if ( strlen( $this->data[$name] ) > $length )
      $this->error( $name, 'max', [ 'length' => $length ] );
  }


  // Validate against a regex
  protected function match( $name, $regex ) {
    // initialize key
    $key = '';

    // convert default templates
    switch ( $regex ) {
      case 'email':         $key = "_$regex"; $regex = '/\A\S+@.+\.\S+\z/'; break;
      case 'email_address': $key = "_$regex"; $regex = '/\S+@.+\.\S+/';     break;
      case 'alpha':         $key = "_$regex"; $regex = '/\A[a-z]+\z/';      break;
      case 'ALPHA':         $key = "_$regex"; $regex = '/\A[A-Z]+\z/';      break;
      case 'Alpha':         $key = "_$regex"; $regex = '/\A[a-z]+\z/i';     break;
      case 'alphanumeric':  $key = "_$regex"; $regex = '/\A[a-z0-9]+\z/';   break;
      case 'ALPHANUMERIC':  $key = "_$regex"; $regex = '/\A[A-Z0-9]+\z/';   break;
      case 'Alphanumeric':  $key = "_$regex"; $regex = '/\A[A-Z0-9]+\z/i';  break;
    }

    if ( ! empty( $this->data[$name] ) && ! preg_match( $regex, $this->data[$name] ) )
      $this->error( $name, "match$key" );
  }


  // Validate confirmation
  protected function confirmation( $name, $confirmation ) {
    // define confirming field name
    $c = is_string( $confirmation ) ? $confirmation : "{$name}_confirmation";

    if ( ! isset( $this->data[$c] ) || $this->data[$name] != $this->data[$c] )
      $this->error( $name, 'confirmation' );
  }


  // Validate against an exact value
  protected function exactly( $name, $expectation ) {
    if ( $this->data[$name] != $expectation )
      $this->error( $name, 'exactly', [ 'expectation' => $expectation ] );
  }


  // Validate as item in an array
  protected function in( $name, $list ) {
    if ( ! in_array( $this->data[$name], $list ) )
      $this->error( $name, 'in', [ 'list' => $list ] );
  }


  // Validate as item not in an array
  protected function not_in( $name, $list ) {
    if ( in_array( $this->data[$name], $list ) )
      $this->error( $name, 'not_in', [ 'list' => $list ] );
  }


  // Validate numeric value
  protected function numeric( $name, $expectation ) {
    // define number and key
    $number = $this->data[$name];
    $key    = 'numeric';

    // ensure string value for expectation
    if ( is_bool( $expectation ) )
      $expectation = 'default';

    // detect expectation
    switch ( $expectation ) {
      case 'even':
        $valid = fmod( $number, 2 ) == 0;

        $key .= '_even';
      break;
      case 'odd':
        $valid = fmod( $number, 2 ) != 0;

        $key .= '_odd';
      break;
      case 'integer':
        if ( is_string( $number ) && preg_match( '/\A(\d+)\z/', $number, $m ) )
          $number = intval( $number );

        $valid = is_int( $number );

        $key .= '_integer';
      break;
      default:
        $valid = is_numeric( $number );
      break;
    }
    
    // ensure validity
    if ( $valid !== true )
      $this->error( $name, $key, [ 'expectation' => $expectation ] );
  }


  // Uniqueness tester
  protected function unique( $name, $options ) {
    // build new query
    $class   = $this->class;
    $records = $class::where( $name, '=', $this->data[$name] );

    // add id to ignore if existant
    if ( $this->exists && $this->model->id )
      $records = $records->where( 'id', '!=', $this->model->id );
    
    // add scope if given
    if ( isset( $options['scope'] ) ) {
      $scope = $options['scope'];

      // get scope value
      $value = isset( $options['value'] ) ?
        $options['value']    : isset( $this->data[$scope] ) ?
        $this->data[$scope]  : isset( $this->model ) ?
        $this->model->$scope : null;

      // define scope
      if ( is_null( $value ) )
        $records = $records->whereNull( $scope );
      else
        $records = $records->where( $scope, '=', $value );
    }
    
    // count number of found records
    $found = $records->count();

    if ( $found > 0 )
      $this->error( $name, 'unique', [ 'found' => $found ] );
  }


  // Custom validation using a closure
  protected function custom( $name, $closure, $message = null ) {
    $passed = is_string( $closure ) ?
      call_user_func( $closure, $name, $this ) :
      $closure( $name, $this );

    if ( ! $passed )
      $this->error( $name, 'custom', [ 'message' => $message ] );
  }


  // Test for empty of blank value
  protected function isBlank( $value ) {
    return preg_match( '/\A(\s+)?\z/', $value );
  }


  // Interpolate values
  protected function interpolate( $message, $values = [] ) {
    // insert all given values
    foreach ( $values as $name => $value ) {
      // ensure string value
      if ( is_array( $value ) )
        $value = implode( ',', $value );

      // add replacement
      $message = preg_replace( "/{{\s?$name\s?}}/", $value, $message );
    }

    return $message;
  }

}

