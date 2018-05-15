<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;

class TestModelWithUploadableMethod extends Model {

  protected $table = 'test_models';
  protected $fillable = [ 'thumb', 'image', 'pdf' ];

  // Uploadable attributes
  protected function uploadable() {
    return [
      'thumb' => [
        'styles' => [
          'tiny' => '128x128#'
        , 'small' => '256x256#'
        , 'medium' => '512x512#'
        , 'large' => '1024x1024#'
        , 'original' => '2560x2560'
        ]
      , 'storage' => 's3'
      ]
    , 'image' => [
        'dir' => 'test_models/{{ id_partition }}/image'
      , 'name' => '{{ basename }}.{{ style }}.{{ ext }}'
      ]
    , 'pdf' => [
        'storage' => 's3_ireland'
      ]
    ];
  }

}
