<!-- writeme -->
Config Perms
============

Allows additional permissions to be created and managed through an administration form.

 * http://drupal.org/project/config_perms
 * Issues: http://drupal.org/project/issues/config_perms
 * Source code: http://cgit.drupalcode.org/config_perms
 * Keywords: Drupal
 * Package name: drupal/config_perms


### Requirements

 * drupal/core ^8 || ^9 || ^10


### License

GPL-2.0+

<!-- endwriteme -->

### Description

This light-weight module allows additional permissions to be created and
managed through an administration form.

It uses the route permissions system to allow or disalow access to paths.

On the administration page a user is able to create a permission with name and
path(s).

These permissions can then be assigned to roles on the permissions page.

### Installation

[Install as usual with composer](https://www.drupal.org/documentation/install/modules-themes/modules-8), or alternatively place the entirety of this
directory within the /modules folder of your Drupal installation.

Navigate to Administer > Extend.

Check the 'Enabled' box next to the 'Custom permissions' module and then

click the 'Save Configuration' button at the bottom.

### Configuration

Add/Edit/Delete permissions in /admin/people/custom-permissions/list.


This project has been sponsored by:

 * [utiks](http://www.utiks.com)
 * [Agaric Technology Collective](https://agaric.coop/)
 * [Drutopia](https://drutopia.org)
