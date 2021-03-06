<?php

/**
 * @file
 * Definition of Drupal\views\Plugin\views\wizard\Standard.
 */

namespace Drupal\views\Plugin\views\wizard;

use Drupal\views\Annotation\ViewsWizard;
use Drupal\Core\Annotation\Translation;

/**
 * @ViewsWizard(
 *   id = "standard",
 *   derivative = "Drupal\views\Plugin\Derivative\DefaultWizardDeriver",
 *   title = @Translation("Default wizard")
 * )
 */
class Standard extends WizardPluginBase {

}
