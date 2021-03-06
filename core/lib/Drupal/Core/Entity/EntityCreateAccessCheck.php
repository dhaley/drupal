<?php

/**
 * @file
 * Contains \Drupal\Core\Entity\EntityCreateAccessCheck.
 */

namespace Drupal\Core\Entity;

use Drupal\Core\Access\StaticAccessCheckInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;

/**
 * Defines an access checker for entity creation.
 */
class EntityCreateAccessCheck implements StaticAccessCheckInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The key used by the routing requirement.
   *
   * @var string
   */
  protected $requirementsKey = '_entity_create_access';

  /**
   * Constructs a EntityCreateAccessCheck object.
   *
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   *   The entity manager.
   */
  public function __construct(EntityManager $entity_manager) {
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function appliesTo() {
    return array($this->requirementsKey);
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request) {
    list($entity_type, $bundle) = explode(':', $route->getRequirement($this->requirementsKey) . ':');

    // The bundle argument can contain request argument placeholders like
    // {name}, loop over the raw variables and attempt to replace them in the
    // bundle name. If a placeholder does not exist, it won't get replaced.
    if ($bundle && strpos($bundle, '{') !== FALSE) {
      foreach ($request->get('_raw_variables')->all() as $name => $value) {
        $bundle = str_replace('{' . $name . '}', $value, $bundle);
      }
      // If we were unable to replace all placeholders, deny access.
      if (strpos($bundle, '{') !== FALSE) {
        return static::DENY;
      }
    }
    return $this->entityManager->getAccessController($entity_type)->createAccess($bundle) ? static::ALLOW : static::DENY;
  }

}
