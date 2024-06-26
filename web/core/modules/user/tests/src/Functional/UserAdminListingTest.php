<?php

declare(strict_types=1);

namespace Drupal\Tests\user\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the user admin listing if views is not enabled.
 *
 * @group user
 * @see user_admin_account()
 */
class UserAdminListingTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests the listing.
   */
  public function testUserListing() {
    // Ensure the anonymous user cannot access the admin listing.
    $this->drupalGet('admin/people');
    $this->assertSession()->statusCodeEquals(403);

    // Create a bunch of users.
    $accounts = [];
    for ($i = 0; $i < 3; $i++) {
      $account = $this->drupalCreateUser();
      $accounts[$account->label()] = $account;
    }
    // Create a blocked user.
    $account = $this->drupalCreateUser();
    $account->block();
    $account->save();
    $accounts[$account->label()] = $account;

    // Create a user at a certain timestamp.
    $account = $this->drupalCreateUser();
    $account->created = 1363219200;
    $account->save();
    $accounts[$account->label()] = $account;
    $timestamp_user = $account->label();

    $rid_1 = $this->drupalCreateRole([], 'custom_role_1', 'custom_role_1');
    $rid_2 = $this->drupalCreateRole([], 'custom_role_2', 'custom_role_2');

    $account = $this->drupalCreateUser();
    $account->addRole($rid_1)->addRole($rid_2)->save();
    $accounts[$account->label()] = $account;
    $role_account_name = $account->label();

    // Create an admin user and look at the listing.
    $admin_user = $this->drupalCreateUser(['administer users']);
    $accounts[$admin_user->label()] = $admin_user;

    $accounts['admin'] = User::load(1);

    $this->drupalLogin($admin_user);

    // Ensure the admin user can access the admin listing.
    $this->drupalGet('admin/people');
    $this->assertSession()->statusCodeEquals(200);

    $result = $this->xpath('//table[contains(@class, "responsive-enabled")]/tbody/tr');
    $result_accounts = [];
    foreach ($result as $account) {
      $account_columns = $account->findAll('css', 'td');
      $name = $account_columns[0]->find('css', 'a')->getText();
      $roles = [];
      $account_roles = $account_columns[2]->findAll('css', 'td ul li');
      if (!empty($account_roles)) {
        foreach ($account_roles as $element) {
          $roles[] = $element->getText();
        }
      }

      $result_accounts[$name] = [
        'name' => $name,
        'status' => $account_columns[1]->getText(),
        'roles' => $roles,
        'member_for' => $account_columns[3]->getText(),
        'last_access' => $account_columns[4]->getText(),
      ];
    }

    $this->assertEmpty(array_keys(array_diff_key($result_accounts, $accounts)), 'Ensure all accounts are listed.');
    foreach ($result_accounts as $name => $values) {
      $this->assertEquals($accounts[$name]->status->value, $values['status'] == 'active');
    }

    $expected_roles = ['custom_role_1', 'custom_role_2'];
    $this->assertEquals($expected_roles, $result_accounts[$role_account_name]['roles'], 'Ensure roles are listed properly.');

    $this->assertEquals(\Drupal::service('date.formatter')->formatTimeDiffSince($accounts[$timestamp_user]->created->value), $result_accounts[$timestamp_user]['member_for'], 'Ensure the right member time is displayed.');

    $this->assertEquals('never', $result_accounts[$timestamp_user]['last_access'], 'Ensure the last access time is "never".');
  }

}
