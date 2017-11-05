<?php

namespace Bulckens\AppTools\Interfaces;

interface UploadInterface {

  public function __construct( $source, $options = [] );
  public function mime();
  public function meta();
  public function isImage();

}