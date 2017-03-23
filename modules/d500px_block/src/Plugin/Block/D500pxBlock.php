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
 * Provides d500px Block.
 *
 * @Block(
 *   id = "d500px_block",
 *   admin_label = @Translation("500px block"),
 * )
 */
class D500pxBlock extends BlockBase implements BlockPluginInterface {

  /**
   * Overrides \Drupal\Component\Plugin\PluginBase::__construct().
   *
   * Overrides the construction of context aware plugins to allow for
   * unvalidated constructor based injection of contexts.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->d500pxintegration = \Drupal::service('d500px.d500pxintegration');
    $this->d500pxconfig = \Drupal::config('d500px.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // I would build the config in a separate method and don't do checks here.
    // Make this very short.
    $config = $this->getConfiguration();

    $params = array(
      'feature'       => $config['feature'],
      'rpp'           => $config['rpp'],
      'image_size'    => $config['image_size'],
      'sort'          => $config['sort'],
    );

    // Add category if its not all.
    if ($config['only'] != '- All -') {
      $params += array('only' => $config['only']);
    }

    // Add username.
    if (!empty($config['username'])) {
      $params += array('username' => $config['username']);
    }

    // Get some pics.
    // Error handling, what if $content is NULL?
    $content = $this->d500pxintegration->getPhotos($params);
    if (empty($content)) {
      $build['#markup'] = $this->t('No Pics!');
      return $build;
    }
    // Don't we need caching? This is ugly...
    $build = array('#cache' => array('max-age' => 0));
    $build['content'] = $content;

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['d500px_block_block_common'] = array(
      '#type'               => 'fieldset',
      '#title'              => $this->t('500px Block Settings'),
      '#collapsible'        => FALSE,
      '#collapsed'          => FALSE,
    );

    $form['d500px_block_block_common']['rpp'] = array(
      '#type'               => 'select',
      '#title'              => $this->t('Number of photos to display?'),
      '#options'            => array_combine(range(5, 100, 5), range(5, 100, 5)),
      '#default_value'      => isset($config['rpp']) ? $config['rpp'] : 5,
      '#description'        => $this->t('The number of results to return. Can not be over 100, default is 5.'), // Use a variable here.
    );

    $form['d500px_block_block_common']['feature'] = array(
       '#type'               => 'select',
       '#title'              => $this->t('Photo stream to be retrieved?'),
       '#options'            => $this->d500pxintegration->d500px_available_features(),
       '#default_value'      => isset($config['feature']) ? $config['feature'] : 'fresh_today',
       '#description'        => $this->t('Photo stream to be retrieved. Default fresh_today.'),
    );

    $image_options_available = $this->d500pxintegration->d500px_photo_get_sizes();
    foreach ($image_options_available as $image_option_key => $value) {
      $image_options[$image_option_key] = $value['width'] . 'x' . $value['height'];
    }

    $form['d500px_block_block_common']['image_size'] = array(
      '#type'               => 'select',
      '#title'              => $this->t('Thumbnail size:'),
      '#options'            => $image_options,
      '#default_value'      => isset($config['image_size']) ? $config['image_size'] : 2,
      '#description'        => $this->t('The photo size to be displayed.'),
    );

    // Error checking, what if NULL?
    $available_categories = $this->d500pxintegration->d500px_available_categories();
    foreach ($available_categories as $key => $value) {
      $categories[$value] = $this->t($value);
    }

    $form['d500px_block_block_common']['only'] = array(
      '#type'               => 'select',
      '#title'              => $this->t('Photo Category'),
      '#options'            => $categories,
      '#default_value'      => isset($config['only']) ? $config['only'] : '- All -',
      '#description'        => $this->t('If you want results from a specific category'),
    );

    $form['d500px_block_block_common']['sort'] = array(
      '#type'               => 'select',
      '#title'              => $this->t('Sort photos in the specified order'),
      '#options'            => $this->d500pxintegration->d500px_available_sort_options(),
      '#default_value'      => isset($config['sort']) ? $config['sort'] : 'created_at',
      '#description'        => t('Sort photos in the specified order'),
      //'#required'           => TRUE,
    );

    $form['d500px_block_block_common']['username'] = array(
      '#type'               => 'textfield',
      '#title'              => t('Username'),
      '#default_value'      => isset($config['username']) ? $config['username'] : '',
      '#description'        => t('All per-user streams require a user_id or username parameter.'),
      '#element_validate'   => array(array($this, 'usernameElementValidator')),
    );

    return $form;
  }

  // Why is this a static function?
  // Add function info.
  public static function usernameElementValidator(&$element, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    if (empty($element['#value']) && ($values['settings']['d500px_block_block_common']['feature'] === 'user'
        || $values['settings']['d500px_block_block_common']['feature'] === 'user_friends')
        ) {
      $form_state->setError($element, t("Additional parameter 'username' is required"));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['rpp'] = $form_state->getValue(array('d500px_block_block_common', 'rpp'));
    $this->configuration['feature'] = $form_state->getValue(array('d500px_block_block_common', 'feature'));
    $this->configuration['image_size'] = $form_state->getValue(array('d500px_block_block_common', 'image_size'));
    $this->configuration['only'] = $form_state->getValue(array('d500px_block_block_common', 'only'));
    $this->configuration['sort'] = $form_state->getValue(array('d500px_block_block_common', 'sort'));
    $this->configuration['username'] = $form_state->getValue(array('d500px_block_block_common', 'username'));
  }

}
