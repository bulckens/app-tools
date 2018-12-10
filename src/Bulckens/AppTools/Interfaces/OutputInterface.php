<?php

namespace Bulckens\AppTools\Interfaces;

interface OutputInterface {

  public function render( $format );
  public function toArray();

}