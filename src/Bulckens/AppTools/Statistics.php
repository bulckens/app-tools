<?php

namespace Bulckens\AppTools;

use Bulckens\Helpers\TimeHelper;
use Bulckens\Helpers\MemoryHelper;

class Statistics {

  protected static $env;
  protected static $start_time;
  protected static $start_memory;

  public function __construct( $env ) {
    // get current situation
    self::$env          = $env;
    self::$start_time   = TimeHelper::milliseconds();
    self::$start_memory = MemoryHelper::snapshot();
  }

  // Get start time
  public static function startTime() {
    return self::$start_time;
  }

  // Get used time
  public static function usedTime() {
    return ( TimeHelper::milliseconds() - self::$start_time ) . 'ms';
  }

  // Get start memory
  public static function startMemory() {
    return MemoryHelper::humanize( self::$start_memory );
  }

  // Get used memory
  public static function usedMemory() {
    return MemoryHelper::humanize( MemoryHelper::snapshot() - self::$start_memory );
  }

  // Get end memory
  public static function endMemory() {
    return MemoryHelper::humanize( MemoryHelper::snapshot() );
  }

  // Get status as array
  public static function toArray( $external = [] ) {
    return array_replace( $external, [
      'env'          => self::$env
    , 'start_time'   => self::startTime()
    , 'used_time'    => self::usedTime()
    , 'start_memory' => self::startMemory()
    , 'used_memory'  => self::usedMemory()
    , 'end_memory'   => self::endMemory()
    ]);
  }


}