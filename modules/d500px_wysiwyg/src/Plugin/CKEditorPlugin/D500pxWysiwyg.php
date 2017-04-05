<?php

namespace Drupal\d500px_wysiwyg\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "drupallink" plugin.
 *
 * @CKEditorPlugin(
 *   id = "d500px_wysiwyg",
 *   label = @Translation("500px Photo wysiwyg")
 * )
 */
class D500pxWysiwyg extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {
    return drupal_get_path('module', 'd500px_wysiwyg') . '/plugin/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return array(
      'core/drupal.ajax',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $path = drupal_get_path('module', 'd500px_wysiwyg') . '/plugin';
    return array(
      'd500px_photo' => array(
        'label' => t('500px Photo'),
        'image' => $path . '/icon.png',
      ),
    );
  }

}
