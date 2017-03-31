<?php

namespace Drupal\d500px_filter\Plugin\Filter;

use Drupal\Component\Utility\Xss;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a filter to insert 500px photo.
 *
 * @Filter(
 *   id = "d500px_filter",
 *   title = @Translation("Embed 500px photo"),
 *   description = @Translation("Allow users to embed a picture from 500px website in an editable content area."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   settings = {
 *     "d500px_filter_width" = 200,
 *     "d500px_filter_height" = 200,
 *   },
 * )
 */
class D500pxFilter extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $new_text = preg_replace_callback(
      '/\[d500px((?:\s).*)]/i',
      function ($matches) {
        $retval = '';
        if (isset($matches[1])) {
          $attrs = explode(' ', trim($matches[1]));
          $vars = [];
          foreach ($attrs as $attr) {
            list($name, $val) = explode('=', trim($attr), 2);
            $vars[Xss::filter($name)] = Xss::filter($val);
          }

          // Check if the source was set.
          if (!isset($vars['photoid'])) {
            return $retval;
          }

          $id = $vars['photoid'];

          $d500pxphotos = \Drupal::service('d500px.d500pxphotos');
          $content = $d500pxphotos->getPhotos($params, $config['nsfw']);
          if (!is_array($content)) {
            return $retval;
          }

          $retval = render($content);
        }
        return $retval;
      },
      $text
    );

    return new FilterProcessResult($new_text);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // TODO refactor this to use standard sizes from D500pxHelpers::photoGetSizes
    $form['d500px_filter_width'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default width of embed'),
      '#description' => $this->t('The default width of the embedded 500px photo (in pixels) to use if not specified in the embed tag.'),
      '#default_value' => $this->settings['d500px_filter_width'],
    ];

    // TODO refactor this to use standard sizes from D500pxHelpers::photoGetSizes
    $form['d500px_filter_height'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default height of embed'),
      '#description' => $this->t('The default height of the embedded 500px photo (in pixels) to use if not specified in the embed tag.'),
      '#default_value' => $this->settings['d500px_filter_height'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    if ($long) {
      return $this->t(
        'Embed 500px photo using @embed. Values for width and height are optional, if left off the default values configured on the %filter input filter will be used',
        [
          '@embed' => '[d500px photoid=<photo_id> width=<width> height=<height>]',
          '%filter' => 'Embed 500px photo',
        ]
      );
    }
    else {
      return $this->t('Embed 500px photo using @embed', ['@embed' => '[d500px photoid=<photo_id> width=<width> height=<height>]']);
    }
  }

}
