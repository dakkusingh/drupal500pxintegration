<?php
/**
 * @file
 * Contains Drupal\d500px_block\Plugin\Block\D500pxBlock.
 */
namespace Drupal\d500px_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
#use Drupal\Core\Url;
#use Drupal\Core\Link;

/**
 * Display all instances for 'YourBlock' block plugin.
 *
 * @Block(
 *   id = "d500px_block",
 *   admin_label = @Translation("Your block"),
 *   deriver = "Drupal\d500px_block\Plugin\Derivative\D500pxBlock"
 * )
 */
class D500pxBlock extends BlockBase {
  public function build() {
    //$build = array();

    return array(
      '#markup' => $this->t('Hello, World!'),
    );
  }
}
