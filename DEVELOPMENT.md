# Development documentation for Custom Permissions (`config_perms`)

Custom Permissions works by completely taking over access control for any route that it defines.

That takeover happens in `src/Routing/RouteSubscriber.php`

(This used to be named "ConfigPermsAccessCheck" but we changed the name to avoid any implication that it implements `/core/lib/Drupal/Core/Access/AccessCheckInterface.php 
