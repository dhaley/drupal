<?php

/**
 * @file
 * Definition of Drupal\file\Tests\DeleteTest.
 */

namespace Drupal\file\Tests;

/**
 * Deletion related tests.
 */
class DeleteTest extends FileManagedTestBase {
  public static function getInfo() {
    return array(
      'name' => 'File delete',
      'description' => 'Tests the file delete function.',
      'group' => 'File API',
    );
  }

  /**
   * Tries deleting a normal file (as opposed to a directory, symlink, etc).
   */
  function testUnused() {
    $file = $this->createFile();

    // Check that deletion removes the file and database record.
    $this->assertTrue(is_file($file->getFileUri()), 'File exists.');
    $file->delete();
    $this->assertFileHooksCalled(array('delete'));
    $this->assertFalse(file_exists($file->getFileUri()), 'Test file has actually been deleted.');
    $this->assertFalse(file_load($file->id()), 'File was removed from the database.');
  }

  /**
   * Tries deleting a file that is in use.
   */
  function testInUse() {
    $file = $this->createFile();
    file_usage()->add($file, 'testing', 'test', 1);
    file_usage()->add($file, 'testing', 'test', 1);

    file_usage()->delete($file, 'testing', 'test', 1);
    $usage = file_usage()->listUsage($file);
    $this->assertEqual($usage['testing']['test'], array(1 => 1), 'Test file is still in use.');
    $this->assertTrue(file_exists($file->getFileUri()), 'File still exists on the disk.');
    $this->assertTrue(file_load($file->id()), 'File still exists in the database.');

    // Clear out the call to hook_file_load().
    file_test_reset();

    file_usage()->delete($file, 'testing', 'test', 1);
    $usage = file_usage()->listUsage($file);
    $this->assertFileHooksCalled(array('load', 'update'));
    $this->assertTrue(empty($usage), 'File usage data was removed.');
    $this->assertTrue(file_exists($file->getFileUri()), 'File still exists on the disk.');
    $file = file_load($file->id());
    $this->assertTrue($file, 'File still exists in the database.');
    $this->assertTrue($file->isTemporary(), 'File is temporary.');
    file_test_reset();

    // Call system_cron() to clean up the file. Make sure the timestamp
    // of the file is older than DRUPAL_MAXIMUM_TEMP_FILE_AGE.
    db_update('file_managed')
      ->fields(array(
        'timestamp' => REQUEST_TIME - (DRUPAL_MAXIMUM_TEMP_FILE_AGE + 1),
      ))
      ->condition('fid', $file->id())
      ->execute();
    drupal_cron_run();

    // system_cron() loads
    $this->assertFileHooksCalled(array('delete'));
    $this->assertFalse(file_exists($file->getFileUri()), 'File has been deleted after its last usage was removed.');
    $this->assertFalse(file_load($file->id()), 'File was removed from the database.');
  }
}
