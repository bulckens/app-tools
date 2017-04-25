<?php

namespace Bulckens\AppTools\Traits;

trait Status {

  protected $status = 200;

  // Return status code
  public function status( $status = null ) {
    // act as getter
    if ( is_null( $status ) )
      return $this->status;

    // act as setter
    $this->status = $status;

    return $this;
  }


  // Check of status code is ok (everything within the 200 and 300 codes)
  public function ok() {
    return $this->status < 400;
  }
  

}