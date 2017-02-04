<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\View;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ViewSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(View::class);
    }
}
