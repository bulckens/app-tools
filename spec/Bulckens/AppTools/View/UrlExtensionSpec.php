<?php

namespace spec\Bulckens\AppTools\View;

use Bulckens\AppTools\View\UrlExtension;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UrlExtensionSpec extends ObjectBehavior {
  
  // GetFunctions method
  function it_returns_an_array_of_functions() {
    $this->getFunctions()->shouldBeArray();
  }

  function it_returns_only_twig_simple_functions() {
    $functions = $this->getFunctions();
    $functions->shouldHaveCount( 4 );
    $functions[0]->shouldHaveType( 'Twig_SimpleFunction' );
    $functions[1]->shouldHaveType( 'Twig_SimpleFunction' );
    $functions[2]->shouldHaveType( 'Twig_SimpleFunction' );
    $functions[3]->shouldHaveType( 'Twig_SimpleFunction' );
  }

}
  