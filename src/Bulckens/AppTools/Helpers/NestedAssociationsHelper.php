<?php

namespace Bulckens\AppTools\Helpers;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class NestedAssociationsHelper {


  // Test a relation to one, whether or not polymorphic
  public static function hasOne( $relation ) {
    return $relation instanceof HasOne || $relation instanceof MorphOne;
  }

  // Test a relation to many, whether or not polymorphic
  public static function hasMany( $relation ) {
    return $relation instanceof HasMany || $relation instanceof MorphMany;
  }


}
