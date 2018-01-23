<?php

namespace Bulckens\AppTools\Notifier;

use Monolog\Formatter\LineFormatter;

class MonologPrintRLineFormatter extends LineFormatter {

  public function format( array $record ) {
    return $record['message'];
  }

}