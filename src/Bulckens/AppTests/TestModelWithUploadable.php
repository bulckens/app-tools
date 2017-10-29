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
    ]
  , 'image' => true
  , 'pdf' => true
  ];


  // Rules related to upload
  public function rules() {
    return [
      'thumb' => [
        'weight' => [ 'min' => '100 KB', 'max' => '2 MB' ]
      , 'dimensions' => [ 'min' => '128x128', 'max' => '2560x2560' ]
      , 'mime' => [ 'image/jpeg', 'image/png', 'image/gif' ]
      ]
    , 'image' => [
        'weight' => '5MB'
      , 'required' => true
      ]
    , 'pdf' => [
        'mime' => 'application/pdf'
      ]
    ];
  }

}