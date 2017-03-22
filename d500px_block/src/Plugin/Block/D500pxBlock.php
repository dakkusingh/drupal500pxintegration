<?php
/**
 * @file
 * Contains Drupal\d500px_block\Plugin\Block\D500pxBlock.
 */
namespace Drupal\d500px_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Display all instances for 'YourBlock' block plugin.
 *
 * @Block(
 *   id = "d500px_block",
 *   admin_label = @Translation("Your block"),
 *   deriver = "Drupal\d500px_block\Plugin\Derivative\D500pxBlock"
 * )
 */
class D500pxBlock extends BlockBase implements BlockPluginInterface {
  public function build() {
    $block_id = $this->getDerivativeId();
    $config = $this->getConfiguration();
    $d500pxintegration = \Drupal::service('d500px.d500pxintegration');

    $build = array('#cache' => array('max-age' => 0));

    $params = array(
      //'feature'       => $block_settings['feature'],
      'rpp'           => $config['rpp'],
      //'image_size[]'  => range(1, 4), // better to get all sizes and deal with requested size at theme level
      //'sort'          => $block_settings['sort'],
    );

    // add category if its not all
    /*if ($block_settings['only'] != '- All -') {
      $params += array('only' => $block_settings['only']);
    }

    // add username
    if (!empty($block_settings['username'])) {
      $params += array('username' => $block_settings['username']);
    }*/



    // get some pics
    $content = $d500pxintegration->getPhotos($params);
    
    // check if there are any photos firstly
    if (empty($content)) {
      return $build['#markup'] = $this->t('No Pics!');
    }

    $build['content'] = $content;
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();
    $form['d500px_block_block_common']['rpp'] = array(
      '#type'               => 'select',
      '#title'              => $this->t('Number of photos to display?'),
      '#options'            => array_combine(range(5,100,5),range(5,100,5)),
      '#default_value'      => isset($config['rpp']) ? $config['rpp'] : 5,
      '#description'        => $this->t('The number of results to return. Can not be over 100, default is 5.'),
      //'#required'           => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['rpp'] = $form_state->getValue(array('d500px_block_block_common', 'rpp'));
  }
}
