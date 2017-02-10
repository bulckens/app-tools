<?php

namespace spec\Bulckens\AppTools\Notifier;

use Bulckens\AppTools\Notifier\MonologPrintRLineFormatter;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MonologPrintRLineFormatterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MonologPrintRLineFormatter::class);
    }
}
