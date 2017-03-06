<?php

namespace spec\Bulckens\AppTools\View;

use Bulckens\AppTools\View\StringExtension;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StringExtensionSpec extends ObjectBehavior {

  // GetFilters method
  function it_returns_an_array_of_functions() {
    $this->getFilters()->shouldBeArray();
  }


  // Adds three filters
  function it_returns_only_twig_simple_functions() {
    $filters = $this->getFilters();
    $filters->shouldHaveCount( 3 );
    $filters[0]->shouldHaveType( 'Twig_SimpleFilter' );
    $filters[1]->shouldHaveType( 'Twig_SimpleFilter' );
    $filters[2]->shouldHaveType( 'Twig_SimpleFilter' );
  }

}
