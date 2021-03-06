<?php

/**
 * @file
 * Contains \Drupal\forum\ForumBreadcrumbBuilder.
 */

namespace Drupal\forum;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderBase;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Entity\EntityManager;
use Drupal\forum\ForumManagerInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Class to define the forum breadcrumb builder.
 */
class ForumBreadcrumbBuilder extends BreadcrumbBuilderBase {

  /**
   * Configuration object for this builder.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Stores the Entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The forum manager service.
   *
   * @var \Drupal\forum\ForumManagerInterface
   */
  protected $forumManager;

  /**
   * Constructs a new ForumBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Entity\EntityManager
   *   The entity manager.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The configuration factory.
   * @param \Drupal\forum\ForumManagerInterface $forum_manager
   *   The forum manager service.
   */
  public function __construct(EntityManager $entity_manager, ConfigFactory $configFactory, ForumManagerInterface $forum_manager) {
    $this->entityManager = $entity_manager;
    $this->config = $configFactory->get('forum.settings');
    $this->forumManager = $forum_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $attributes) {
    if (!empty($attributes[RouteObjectInterface::ROUTE_NAME])) {
      $route_name = $attributes[RouteObjectInterface::ROUTE_NAME];
      if ($route_name == 'node.view' && isset($attributes['node'])) {
        if ($this->forumManager->checkNodeType($attributes['node'])) {
          return $this->forumPostBreadcrumb($attributes['node']);
        }
      }
      if ($route_name == 'forum.page' && isset($attributes['taxonomy_term'])) {
        return $this->forumTermBreadcrumb($attributes['taxonomy_term']);
      }
    }
  }

  /**
   * Builds the breadcrumb for a forum post page.
   */
  protected function forumPostBreadcrumb($node) {
    $vocabulary = $this->entityManager->getStorageController('taxonomy_vocabulary')->load($this->config->get('vocabulary'));

    $breadcrumb[] = $this->l($this->t('Home'), '<front>');
    $breadcrumb[] = l($vocabulary->label(), 'forum');
    if ($parents = taxonomy_term_load_parents_all($node->forum_tid)) {
      $parents = array_reverse($parents);
      foreach ($parents as $parent) {
        $breadcrumb[] = $this->l($parent->label(), 'forum.page', array('taxonomy_term' => $parent->id()));
      }
    }
    return $breadcrumb;
  }

  /**
   * Builds the breadcrumb for a forum term page.
   */
  protected function forumTermBreadcrumb($term) {
    $vocabulary = $this->entityManager->getStorageController('taxonomy_vocabulary')->load($this->config->get('vocabulary'));

    $breadcrumb[] = $this->l($this->t('Home'), '<front>');
    if ($term->tid) {
      // Parent of all forums is the vocabulary name.
      $breadcrumb[] = $this->l($vocabulary->label(), 'forum.index');
    }
    // Add all parent forums to breadcrumbs.
    if ($term->parents) {
      foreach (array_reverse($term->parents) as $parent) {
        if ($parent->id() != $term->id()) {
          $breadcrumb[] = $this->l($parent->label(), 'forum.page', array('taxonomy_term' => $parent->id()));
        }
      }
    }
    return $breadcrumb;
  }

}
