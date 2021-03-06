<?php

/**
 * @file
 * Contains \Drupal\comment\CommentManagerInterface.
 */

namespace Drupal\comment;

/**
 * Comment manager contains common functions to manage comment fields.
 */
interface CommentManagerInterface {

  /**
   * Utility function to return URI of the comment's parent entity.
   *
   * @param \Drupal\comment\CommentInterface $comment
   *   The comment entity.
   *
   * @return array
   *   An array returned by \Drupal\Core\Entity\EntityInterface::uri().
   */
  public function getParentEntityUri(CommentInterface $comment);

  /**
   * Utility function to return an array of comment fields.
   *
   * @param string $entity_type
   *   The entity type to return fields which are attached on.
   *
   * @return array
   *   An array of comment field map definitions, keyed by field name. Each
   *   value is an array with two entries:
   *   - type: The field type.
   *   - bundles: The bundles in which the field appears, as an array with entity
   *     types as keys and the array of bundle names as values.
   *
   * @see field_info_field_map()
   */
  public function getFields($entity_type = NULL);

  /**
   * Utility function to return all comment fields.
   */
  public function getAllFields();

  /**
   * Utility method to add the default comment field to an entity.
   *
   * Attaches a comment field named 'comment' to the given entity type and
   * bundle. Largely replicates the default behaviour in Drupal 7 and earlier.
   *
   * @param string $entity_type
   *   The entity type to attach the default comment field to.
   * @param string $bundle
   *   The bundle to attach the default comment field instance to.
   * @param string $field_name
   *   (optional) Field name to use for the comment field. Defaults to 'comment'.
   * @param int $default_value
   *   (optional) Default value, one of COMMENT_HIDDEN, COMMENT_OPEN,
   *   COMMENT_CLOSED. Defaults to COMMENT_OPEN.
   */
  public function addDefaultField($entity_type, $bundle, $field_name = 'comment', $default_value = COMMENT_OPEN);

  /**
   * Creates a comment_body field instance.
   *
   * @param string $entity_type
   *   The type of the entity to which the comment field attached.
   * @param string $field_name
   *   Name of the comment field to add comment_body field.
   */
  public function addBodyField($entity_type, $field_name);

  /**
   * Builds human readable page title for field_ui management screens.
   *
   * @param string $field_name
   *   The comment field for which the overview is to be displayed.
   *
   * @return string
   *   The human readable field name.
   */
  public function getFieldUIPageTitle($field_name);

}
