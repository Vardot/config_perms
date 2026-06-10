<?php

namespace Drupal\config_perms\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\config_perms\Entity\CustomPermsEntity;

/**
 * Subscribes to route alterations for the config_perms module.
 *
 * Overrides route access requirements based on enabled custom permission
 * entities. If a route is matched by a custom permission and the permission
 * is active, the route's access control is replaced by the custom
 * config_perms access checker.
 *
 * @package Drupal\config_perms\Routing
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Alters existing routes to apply config_perms access control.
   *
   * This method loads all enabled CustomPermsEntity instances and applies
   * their route definitions. If a matching route exists, its access
   * requirements are overridden to use the _config_perms_access_check only.
   *
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The collection of defined routes in the system.
   */
  protected function alterRoutes(RouteCollection $collection) {
    $custom_perms = CustomPermsEntity::loadMultiple();
    /** @var \Drupal\config_perms\Entity\CustomPermsEntity $custom_perm */
    foreach ($custom_perms as $custom_perm) {
      if ($custom_perm->getStatus()) {
        $routes = config_perms_parse_path($custom_perm->getRoute());
        foreach ($routes as $route) {
          if ($route = $collection->get($route)) {
            // Override route requirements to use only our custom access check.
            $route->setRequirements(['_config_perms_access_check' => 'TRUE']);
          }
        }
      }
    }
  }

}
