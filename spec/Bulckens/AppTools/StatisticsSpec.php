<?php

namespace spec\Bulckens\AppTools;

use Bulckens\AppTools\Statistics;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StatisticsSpec extends ObjectBehavior {

  function let() {
    $this->beConstructedWith( 'dev' );
  }


  // StartTime method
  function it_returns_the_start_time() {
    $this::startTime()->shouldBeDouble();
  }


  // UsedTime method
  function it_returns_the_used_time() {
    $this::usedTime()->shouldBeString();
    $this::usedTime()->shouldMatch( '/^\d+ms$/' );
  }


  // StartMemory method
  function it_returns_the_start_memory() {
    $this::startMemory()->shouldMatch( '/^[0-9\.]{1,7}\s[a-z]{2,5}$/i' );
  }


  // UsedMemory method
  function it_returns_the_used_memory() {
    $this::usedMemory()->shouldMatch( '/^[0-9\.]{1,7}\s[a-z]{2,5}$/i' );
  }


  // EndMemory method
  function it_returns_the_end_memory() {
    $this::endMemory()->shouldMatch( '/^[0-9\.]{1,7}\s[a-z]{2,5}$/i' );
  }


  // ToArray method
  function it_returns_statistics_as_an_array() {
    $array = $this::toArray();
    $array->shouldBeArray();
    $array->shouldHaveKeyWithValue( 'env', 'dev' );
    $array->shouldHaveKey( 'start_time' );
    $array->shouldHaveKey( 'used_time' );
    $array->shouldHaveKey( 'start_memory' );
    $array->shouldHaveKey( 'used_memory' );
    $array->shouldHaveKey( 'end_memory' );
  }

  function it_returns_statistics_as_an_array_with_given_extra_info() {
    $array = $this::toArray([ 'extra' => 'info' ]);
    $array->shouldBeArray();
    $array->shouldHaveKeyWithValue( 'extra', 'info' );
  }

}
