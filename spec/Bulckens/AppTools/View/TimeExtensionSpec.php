<?php

namespace spec\Bulckens\AppTools\View;

use Bulckens\AppTools\View\TimeExtension;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TimeExtensionSpec extends ObjectBehavior {

  // GetFunctions method
  function it_returns_an_array_of_functions() {
    $this->getFunctions()->shouldBeArray();
  }

  function it_returns_only_twig_simple_functions() {
    $functions = $this->getFunctions();
    $functions[0]->shouldHaveType( 'Twig_SimpleFunction' );
  }

}
