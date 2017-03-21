<?php

/**
 * @file
 * Contains \Drupal\d500px\Controller\d500pxdemo.
 */
namespace Drupal\d500px\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\d500px\D500px;

class d500pxdemo extends ControllerBase {
  public function mainPage() {
    $foo = \Drupal::service('d500px.d500pxintegration');
    ksm($foo->get('photos', array()));
    return [
        '#markup' => $this->t('Something goes here!'),
    ];
  }
}
