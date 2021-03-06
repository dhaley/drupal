<?php

/**
 * @file
 * Contains \Drupal\user\Tests\Entity\UserTest.
 */

namespace Drupal\user\Tests\Entity;

use Drupal\Tests\Core\Session\UserSessionTest;
use Drupal\user\Entity\User;

/**
 * Tests the user object.
 *
 * @see \Drupal\user\Entity\User
 */
class UserTest extends UserSessionTest {

  public static function getInfo() {
    return array(
      'name' => 'User object',
      'description' => 'Tests the user object.',
      'group' => 'User',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function createUserSession(array $rids = array()) {
    $user = $this->getMockBuilder('Drupal\user\Entity\User')
      ->disableOriginalConstructor()
      ->setMethods(array('getRoles', 'id'))
      ->getMock();
    $user->expects($this->any())
      ->method('id')
      // @todo Also test the uid = 1 handling.
      ->will($this->returnValue(0));
    $user->expects($this->any())
      ->method('getRoles')
      ->will($this->returnValue($rids));
    return $user;
  }

}
