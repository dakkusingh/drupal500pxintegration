<?php

/**
 * @file
 * Contains Drupal\D500px\D500pxPhotos.
 */

namespace Drupal\D500px;

use Drupal\d500px\D500pxHelpers;
use Drupal\d500px\D500pxIntegration;

/**
 * 500px Photos class.
 *
 * @package Drupal\D500px
 */
class D500pxPhotos {

  /**
   * @var \Drupal\d500px\D500pxHelpers
   */
  public $d500pxhelpers;

  /**
   * @var \Drupal\d500px\D500pxIntegration
   */
  protected $d500pxintegration;


  /**
   * Constructor for the 500px Photos class.
   */
  public function __construct(D500pxHelpers $d500pxhelpers, D500pxIntegration $d500pxintegration) {
    $this->d500pxhelpers = $d500pxhelpers;
    $this->d500pxintegration = $d500pxintegration;
  }

  /**
   * Helper method to get photos.
   *
   * @param array $parameters
   * @return array
   */
  public function getPhotos($parameters = array(), $nsfw = FALSE) {
    $photos = $this->d500pxintegration->requestD500px('photos', $parameters)->photos;
    $themed_photos = NULL;

    foreach ($photos as $photo_obj) {
      $photo_obj->photo_page_url = $this->d500pxintegration->website_url . $photo_obj->url;
      $themed_photos[] = $this->d500pxhelpers->preparePhoto($photo_obj, $nsfw);
    }

    return array('#theme' => 'd500px_photos', '#photos' => $themed_photos);
  }

}
