<?php

namespace Drupal\config_perms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\config_perms\Entity\CustomPermsEntity;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the admin configuration form for managing custom permissions.
 *
 * Allows site administrators to enable, disable, edit, add, and delete
 * route-based permissions for configuration access.
 *
 * @package Drupal\config_perms\Form
 */
class ConfigPermListForm extends FormBase {

  /**
   * The router provider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routerProvider;

  /**
   * The router builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * Constructs a new ConfigPermListForm object.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $router_provider
   *   The router provider service.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $router_builder
   *   The router builder service.
   */
  public function __construct(RouteProviderInterface $router_provider, RouteBuilderInterface $router_builder) {
    $this->routerProvider = $router_provider;
    $this->routerBuilder = $router_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('router.route_provider'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_perm_list_form';
  }

  /**
   * Builds the permissions administration form.
   *
   * Displays a table of custom permissions with options to modify or add new
   * ones.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The complete form render array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form initialization and description fieldset.
    $form['perms'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Custom Permissions'),
      '#description' => '<p>' . $this->t("Please note that the order in which permissions are granted are as follows:") . '</p>' .
      "<ul>
         <li>" . $this->t("Custom permissions only support routes") . "</li>\n
         <li>" . $this->t("User 1 still maintains full control") . "</li>\n
         <li>" . $this->t("Remove the permission 'Administer site configuration' from roles you wish to give access to only specified custom site configuration permissions") . "</li>\n
        </ul>",
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    // Load all existing custom permissions.
    $perms = CustomPermsEntity::loadMultiple();

    // Table header.
    $header = [
      $this->t('Enabled'),
      $this->t('Name'),
      $this->t('Route(s)'),
      '',
      '',
    ];

    // Setup the editable table.
    $form['perms']['local'] = [
      '#type' => 'table',
      '#header' => $header,
      '#prefix' => '<div id="config_perms-wrapper">',
      '#suffix' => '</div>',
    ];

    // Populate table rows for each permission.
    /** @var \Drupal\config_perms\Entity\CustomPermsEntity $perm */
    foreach ($perms as $key => $perm) {
      $form['perms']['local'][$key] = ['#tree' => TRUE];

      $form['perms']['local'][$key]['status'] = [
        '#type' => 'checkbox',
        '#default_value' => $perm->status(),
      ];
      $form['perms']['local'][$key]['name'] = [
        '#type' => 'textfield',
        '#default_value' => $perm->label(),
        '#size' => 30,
      ];
      $form['perms']['local'][$key]['route'] = [
        '#type' => 'textarea',
        '#default_value' => $perm->getRoute(),
        '#size' => 50,
        '#rows' => 1,
      ];
      $form['perms']['local'][$key]['delete'] = $perm->toLink($this->t('Delete'), 'delete-form')->toRenderable();
      $form['perms']['local'][$key]['id'] = [
        '#type' => 'hidden',
        '#default_value' => $perm->id(),
      ];
    }

    // Initialize new row handling.
    $num_new = $form_state->getValue('num_new') ?? 0;
    $form_state->setValue('num_new', $num_new);

    for ($i = 0; $i < $num_new; $i++) {
      $form['perms']['local']['new'] = [
        'status' => ['#type' => 'checkbox'],
        'name' => ['#type' => 'textfield', '#size' => 30],
        'route' => ['#type' => 'textarea', '#rows' => 2, '#size' => 50],
      ];
    }

    // Add permission button.
    $form['perms']['add']['status'] = [
      '#name' => 'status',
      '#id' => 'edit-local-status',
      '#type' => 'submit',
      '#value' => $this->t('Add permission'),
      '#submit' => ['::configPermsAdminFormAddSubmit'],
      '#ajax' => [
        'callback' => '::configPermsAdminFormAddCallback',
        'wrapper' => 'config_perms-wrapper',
      ],
    ];

    // Save button.
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
    ];

    return $form;
  }

  /**
   * AJAX callback to refresh the table after adding a new permission.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   *
   * @return array
   *   The rendered portion of the form to update.
   */
  public function configPermsAdminFormAddCallback($form, $form_state) {
    return $form['perms']['local'];
  }

  /**
   * Submit handler for the "Add permission" button.
   *
   * Increments the number of new permission rows.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   */
  public function configPermsAdminFormAddSubmit($form, &$form_state) {
    $form_state->setValue('num_new', $form_state->getValue('num_new') + 1);
    $form_state->setRebuild();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $perms = CustomPermsEntity::loadMultiple();

    foreach ($values['local'] as $key => $perm) {
      if (empty($perm['name']) && empty($perm['route']) && $key !== 'new') {
        CustomPermsEntity::load($perm['id'])->delete();
      }
      else {
        if (empty($perm['name'])) {
          $form_state->setErrorByName("local][$key", $this->t("The name cannot be empty."));
        }
        if (empty($perm['route'])) {
          $form_state->setErrorByName("local][$key", $this->t("The route cannot be empty."));
        }
        if (array_key_exists($this->configPermsGenerateMachineName($perm['name']), $perms) && !isset($perm['id'])) {
          $form_state->setErrorByName("local][$key", $this->t("A permission with that name already exists."));
        }
        if (!empty($perm['route'])) {
          $routes = config_perms_parse_path($perm['route']);
          foreach ($routes as $route) {
            if (count($this->routerProvider->getRoutesByNames([$route])) < 1) {
              $form_state->setErrorByName("local][$key", $this->t("The route @route is invalid.", ['@route' => $perm['route']]));
            }
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $perms = CustomPermsEntity::loadMultiple();

    foreach ($values['local'] as $key => $data) {
      if ($key === 'new') {
        $entity = CustomPermsEntity::create();
        $entity->set('id', $this->configPermsGenerateMachineName($data['name']));
      }
      else {
        $entity = $perms[$data['id']];
      }

      $entity->set('label', $data['name']);
      $entity->set('route', $data['route']);
      $entity->set('status', $data['status']);
      $entity->save();
    }

    $this->routerBuilder->rebuild();
    $this->messenger()->addMessage($this->t('The permissions have been saved.'));
  }

  /**
   * Generates a machine name from a string.
   *
   * Converts a string to a lowercase, underscore-separated format suitable
   * for use as an ID.
   *
   * @param string $string
   *   The human-readable string.
   *
   * @return string
   *   The machine-friendly identifier.
   */
  public function configPermsGenerateMachineName($string) {
    return strtolower(preg_replace('/[^a-zA-Z0-9_]+/', '_', $string));
  }

}
