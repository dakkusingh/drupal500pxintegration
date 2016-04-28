<?php

/**
 * @file
 * Contains Drupal\d500px\Form\D500pxSettingsForm.
 */

namespace Drupal\d500px\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements the 500px Settings form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */
class D500pxSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'd500px_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('d500px.config');

    $form['oauth'] = array(
      '#type' => 'fieldset',
      '#title' => t('OAuth Settings'),
      '#access' => Drupal::moduleHandler()->moduleExists('oauth_common')),
      '#description' => t('To enable OAuth based access for 500px, you must <a href="@url">register your application</a> with 500px and add the provided keys here.', array('@url' => 'http://developers.500px.com/settings/applications?from=developers')),
    );

    // @todo was this ever used?
    /*
    $form['oauth']['callback_url'] = array(
      '#type' => 'item',
      '#title' => t('Callback URL'),
      '#markup' => url('d500px/oauth', array('absolute' => TRUE)),
    );
    */

    $form['oauth']['d500px_consumer_key'] = array(
      '#type' => 'textfield',
      '#title' => t('OAuth Consumer key'),
      '#default_value' => $config->get('d500px_consumer_key'),
    );

    $form['oauth']['d500px_consumer_secret'] = array(
      '#type' => 'textfield',
      '#title' => t('OAuth Consumer secret'),
      '#default_value' => $config->get('d500px_consumer_secret'),
    );

    // 500px external APIs settings.
    $form['d500px'] = array(
      '#type' => 'fieldset',
      '#title' => t('500px Settings'),
      '#description' => t('The following settings connect 500px module with external APIs.'),
    );

    $form['d500px']['d500px_host'] = array(
      '#type' => 'textfield',
      '#title' => t('500px Host'),
      '#default_value' => $config->get('d500px_host'),
    );

    $form['d500px']['d500px_api'] = array(
      '#type' => 'textfield',
      '#title' => t('500px API'),
      '#default_value' => $config->get('d500px_api'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('d500px.settings')
      ->set('d500px_consumer_key', $form_state['values']['d500px_consumer_key'])
      ->set('d500px_consumer_secret', $form_state['values']['d500px_consumer_secret'])
      ->set('d500px_host', $form_state['values']['d500px_host'])
      ->set('d500px_api', $form_state['values']['d500px_api'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}

