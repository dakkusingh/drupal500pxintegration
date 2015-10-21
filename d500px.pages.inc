<?php
/**
 * @file
 * Page callbacks for 500px module.
 */
 
 

/**
 * 500px settings form.
 */
function d500px_admin_form($form, &$form_state) {

  $form['oauth'] = array(
    '#type' => 'fieldset',
    '#title' => t('OAuth Settings'),
    '#access' => \Drupal::moduleHandler()->moduleExists('oauth_common'),
    '#description' => t('To enable OAuth based access for 500px, you must <a href="@url">register your application</a> with 500px and add the provided keys here.', array('@url' => 'http://developers.500px.com/settings/applications?from=developers')),
  );

  // @FIXME
// url() expects a route name or an external URI.
// $form['oauth']['callback_url'] = array(
//     '#type' => 'item',
//     '#title' => t('Callback URL'),
//     '#markup' => url('d500px/oauth', array('absolute' => TRUE)),
//   );

  
  $form['oauth']['d500px_consumer_key'] = array(
    '#type' => 'textfield',
    '#title' => t('OAuth Consumer key'),
    '#default_value' => \Drupal::config('d500px.settings')->get('d500px_consumer_key'),
  );  

  $form['oauth']['d500px_consumer_secret'] = array(
    '#type' => 'textfield',
    '#title' => t('OAuth Consumer secret'),
    '#default_value' => \Drupal::config('d500px.settings')->get('d500px_consumer_secret'),
  );

  
  // 500px external APIs settings.
  $form['d500px'] = array(
    '#type' => 'fieldset',
    '#title' => t('500px Settings'),
    '#description' => t('The following settings connect 500px module with external APIs.'),
  );

  // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/d500px.settings.yml and config/schema/d500px.schema.yml.
$form['d500px']['d500px_host'] = array(
    '#type' => 'textfield',
    '#title' => t('500px Host'),
    '#default_value' => \Drupal::config('d500px.settings')->get('d500px_host'),
  );  

  // @FIXME
// Could not extract the default value because it is either indeterminate, or
// not scalar. You'll need to provide a default value in
// config/install/d500px.settings.yml and config/schema/d500px.schema.yml.
$form['d500px']['d500px_api'] = array(
    '#type' => 'textfield',
    '#title' => t('500px API'),
    '#default_value' => \Drupal::config('d500px.settings')->get('d500px_api'),
  );
      
  return system_settings_form($form);
}
