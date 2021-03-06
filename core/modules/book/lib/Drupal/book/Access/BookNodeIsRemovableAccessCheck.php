<?php

/**
 * @file
 * Contains Drupal\book\Access\BookNodeIsRemovableAccessCheck.
 */

namespace Drupal\book\Access;

use Drupal\book\BookManager;
use Drupal\Core\Access\StaticAccessCheckInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Determines whether the requested node can be removed from its book.
 */
class BookNodeIsRemovableAccessCheck implements StaticAccessCheckInterface {

  /**
   * Book Manager Service.
   *
   * @var \Drupal\book\BookManager
   */
  protected $bookManager;

  /**
   * Constructs a BookNodeIsRemovableAccessCheck object.
   *
   * @param \Drupal\book\BookManager $book_manager
   *   Book Manager Service.
   */
  public function __construct(BookManager $book_manager) {
    $this->bookManager = $book_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function appliesTo() {
    return array('_access_book_removable');
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request) {
    $node = $request->attributes->get('node');
    if (!empty($node)) {
      return $this->bookManager->checkNodeIsRemovable($node) ? static::ALLOW : static::DENY;
    }
    return static::DENY;
  }

}
