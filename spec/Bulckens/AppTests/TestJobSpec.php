<?php

namespace spec\Bulckens\AppTests;

use Bulckens\AppTests\TestJob;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TestJobSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(TestJob::class);
    }
}
