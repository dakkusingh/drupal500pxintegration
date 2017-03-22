<?php
/**
 * @file
 * Contains Drupal\d500px_block\Plugin\Block\D500pxBlock.
 */
namespace Drupal\d500px_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
    $block_id = $this->getDerivativeId();

    $build = array('#cache' => array('max-age' => 0));
    $params = array();

    $d500pxintegration = \Drupal::service('d500px.d500pxintegration');

    // get some pics
    $content = $d500pxintegration->getPhotos($params);
    
    /*
    // check if there are any photos firstly
    if (empty($content->photos)) {
      return $build['#markup'] = $this->t('No Pics!');
    }
    */

    $build['#markup'] = $content;
    return $build;
  }


}
