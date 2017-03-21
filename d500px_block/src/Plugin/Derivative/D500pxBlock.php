<?php

namespace Drupal\d500px_block\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

class D500pxBlock extends DeriverBase {
    public function getDerivativeDefinitions($base_plugin_definition) {
      $max = 2;

      for ($count = 0; $count < $max; $count++) {
        $delta = 'd500px_block_'. $count;
        $this->derivatives[$delta] = $base_plugin_definition;

        // block 0 is confusing
        /*$title_count = $count + 1;

        $previous_settings = variable_get($delta, array());

        $title = t('500px: unconfigured block: ') . ' ' . $title_count;

        // TODO: better titles??
        if (!empty($previous_settings['feature'])) {

          // add title and feature
          $title = t('500px: ') . $previous_settings['feature'];

          // user feature? add username
          if ($previous_settings['feature'] == 'user') {
            $title .= ':'. $previous_settings['username'];
          }

          // selected album? add album name
          if ($previous_settings['only'] != '- All -') {
            $title .= ' - '. $previous_settings['only'];
          }

          // finally add number of items
          $title .= ' ('. $previous_settings['rpp'] .')';
        }*/

        $this->derivatives[$delta]['admin_label'] = t('500px Title: @name', array('@name' => $delta));
        }
        return $this->derivatives;

  }
}
