<?php

namespace Bulckens\AppTests;

use Bulckens\AppTools\Model;

class TestModelWithUploadable extends Model {

  protected $table = 'test_models';
  protected $fillable = [ 'thumb', 'image', 'pdf' ];

  // Uploadable attributes
  protected $uploadable = [
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
  , 'image' => true
  , 'pdf' => [
      'storage' => 's3_ireland'
    ]
  ];


  // Rules related to upload
  public function rules() {
    return [
      'thumb' => [
        'weight' => [ 'min' => '10 KB', 'max' => '512 KB' ]
      , 'dimensions' => [ 'min' => '128x128', 'max' => '1024x1024' ]
      , 'mime' => [ 'image/jpeg', 'image/png', 'image/gif' ]
      ]
    , 'image' => [
        'weight' => '512 KB'
      , 'required' => true
      ]
    , 'pdf' => [
        'mime' => 'application/pdf'
      ]
    ];
  }

}