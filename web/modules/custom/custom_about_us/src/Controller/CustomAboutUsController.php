<?php

namespace Drupal\custom_about_us\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for rendering the About Us Leadership page.
 */
class CustomAboutUsController extends ControllerBase {

  /**
   * Builds the About Us Leadership page.
   *
   * @return array
   *   A render array containing the leadership settings and the custom theme.
   */
  public function build() {
    // Load the configuration containing leadership settings.
    $config = $this->config('custom_about_us.settings');
    $leadership_settings = $config->get('leadership_settings') ?: [];

    // Define the render array with the custom theme and leadership settings.
    $output = [
      '#theme' => 'custom_about_us_leadership',
      '#leadership_settings' => $leadership_settings,
    ];

    return $output;
  }
}
