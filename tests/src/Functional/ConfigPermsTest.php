<?php

namespace Drupal\Tests\config_perms\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Functional tests for the config_perms module.
 *
 * Verifies that custom configuration permissions behave as expected,
 * including default behavior, correct access restriction, and permission
 * overrides.
 *
 * @group config_perms
 */
class ConfigPermsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['config_perms'];

  /**
   * Tests access to the custom permissions admin page based on permissions.
   *
   * Verifies that a user with 'administer config permissions' can access
   * the page, while a user without it cannot.
   */
  public function testAdministerConfigPermsPermission() {
    $user_with_permission = $this->drupalCreateUser(['administer config permissions']);
    $user_without_permission = $this->drupalCreateUser();

    // Assert that the user with the permission can administer the module.
    $this->drupalLogin($user_with_permission);
    $this->drupalGet('/admin/people/custom-permissions/list');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalLogout();

    // Assert that the user without the permission cannot access the page.
    $this->drupalLogin($user_without_permission);
    $this->drupalGet('/admin/people/custom-permissions/list');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogout();
  }

  /**
   * Tests that permissions are enforced for accessing account settings.
   *
   * Confirms that a user with the 'Administer account settings' permission
   * can view the settings page, while one without cannot.
   */
  public function testPermissions() {
    $user_with_permission = $this->drupalCreateUser(['Administer account settings']);
    $user_without_permission = $this->drupalCreateUser();

    // Assert that the user with the permission can access the page.
    $this->drupalLogin($user_with_permission);
    $this->drupalGet('admin/config/people/accounts');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Account settings');
    $this->drupalLogout();

    // Assert that the user without permission cannot access the page.
    $this->drupalLogin($user_without_permission);
    $this->drupalGet('admin/config/people/accounts');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogout();
  }

  /**
   * Tests that core permissions can be overridden by config_perms settings.
   *
   * Ensures that a user with the custom 'Administer account settings'
   * permission has access, while a user with only the core permission does not.
   */
  public function testOverridePermissions() {
    $user_custom_permission = $this->drupalCreateUser(['Administer account settings']);
    $user_core_permission = $this->drupalCreateUser(['administer account settings']);

    // Assert that the user with the permission can access the page.
    $this->drupalLogin($user_custom_permission);
    $this->drupalGet('admin/config/people/accounts');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Account settings');
    $this->drupalLogout();

    // Assert that the config_perms overridden the core permission and doesn't
    // allow the user to see the page even having the core permission.
    $this->drupalLogin($user_core_permission);
    $this->drupalGet('admin/config/people/accounts');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalLogout();
  }

}
