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
    '#access' => module_exists('oauth_common'),
    '#description' => t('To enable OAuth based access for 500px, you must <a href="@url">register your application</a> with 500px and add the provided keys here.', array('@url' => 'http://developers.500px.com/settings/applications?from=developers')),
  );



  return system_settings_form($form);
}
