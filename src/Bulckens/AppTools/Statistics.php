<?php

namespace Bulckens\AppTools;

use Bulckens\Helpers\TimeHelper as T;
use Bulckens\Helpers\MemoryHelper as M;

class Statistics {

  protected $start_time;
  protected $start_memory;

  public function __construct() {
    // get current situation
    $this->start_time   = T::milliseconds();
    $this->start_memory = M::snapshot();
  }

  // Get start time
  public function startTime() {
    return $this->start_time;
  }

  // Get used time
  public function usedTime() {
    return ( T::milliseconds() - $this->start_time ) . 'ms';
  }

  // Get start memory
  public function startMemory() {
    return M::humanize( $this->start_memory );
  }

  // Get used memory
  public function usedMemory() {
    return M::humanize( M::snapshot() - $this->start_memory );
  }

  // Get peak memory
  public function peakMemory() {
    return M::humanize( memory_get_peak_usage() );
  }

  // Get end memory
  public function endMemory() {
    return M::humanize( M::snapshot() );
  }

  // Get status as array
  public function toArray( $external = [] ) {
    return array_replace( $external, [
      'start_time'   => $this->startTime()
    , 'used_time'    => $this->usedTime()
    , 'start_memory' => $this->startMemory()
    , 'used_memory'  => $this->usedMemory()
    , 'peak_memory'  => $this->peakMemory()
    , 'end_memory'   => $this->endMemory()
    ]);
  }


}
