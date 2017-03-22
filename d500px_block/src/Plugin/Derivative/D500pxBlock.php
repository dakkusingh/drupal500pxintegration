<?php

namespace Drupal\d500px_block\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

class D500pxBlock extends DeriverBase {
    public function getDerivativeDefinitions($base_plugin_definition) {
      // TODO Extend this to cater for user settings
      $config = \Drupal::config('d500px_block.settings');
      $max = $config->get('d500px_block_number_blocks');

      for ($count = 0; $count < $max; $count++) {
        $delta = 'd500px_block_'. $count;
        $this->derivatives[$delta] = $base_plugin_definition;
        $this->derivatives[$delta]['admin_label'] = t('500px Title: @name', array('@name' => $delta));
      }

      return $this->derivatives;

  }
}
