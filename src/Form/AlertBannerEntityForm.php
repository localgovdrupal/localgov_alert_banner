<?php

namespace Drupal\localgov_alert_banner\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for Alert banner edit forms.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerEntityForm extends ContentEntityForm {

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Module handler service.
   *
   * @var Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Configuration service.
   *
   * @var Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Request stack service.
   *
   * @var Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Theme manager service.
   *
   * @var Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    $instance = parent::create($container);
    $instance->account = $container->get('current_user');
    $instance->moduleHandler = $container->get('module_handler');
    $instance->configFactory = $container->get('config.factory');
    $instance->request = $container->get('request_stack');
    $instance->themeManager = $container->get('theme.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $entity = $this->entity;
    assert($entity instanceof AlertBannerEntity);

    if (!$entity->isNew()) {
      $form['new_revision'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Create new revision'),
        '#default_value' => FALSE,
        '#weight' => 10,
      ];
    }

    // Create the vertical tabs like on node edit forms.
    // @src https://drupal.stackexchange.com/a/276907
    // Only do this if the theme is not gin! Issue #116.
    // @todo Opt alert banner into gin admin theming.
    if ($this->themeManager->getActiveTheme()->getName() != 'gin') {
      $form['#theme'][] = 'node_edit_form';
      $form['#attached']['library'] = ['node/drupal.node'];

      // Support Elbow room module if it's installed.
      if ($this->moduleHandler->moduleExists('elbow_room')) {
        $form['#attached']['library'][] = 'elbow_room/base';
        $elbowRoomConfig = $this->configFactory()->get('elbow_room.settings');
        $form['#attached']['drupalSettings']['elbow_room']['default'] = $elbowRoomConfig->get('default');

        // Add node form classes for elbow room to function.
        $form['#attributes']['class'][] = 'node-form';
      }
    }

    // Create the advanced field group.
    $form['advanced'] = [
      '#type' => 'container',
      '#weight' => 99,
      '#attributes' => [
        'class' => ['entity-meta'],
      ],
    ];

    // Alert details group.
    $form['alert_details'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Alert details'),
      '#open' => TRUE,
      '#weight' => -5,
    ];
    $form['type_of_alert']['#group'] = 'alert_details';
    $form['title']['#group'] = 'alert_details';
    $form['short_description']['#group'] = 'alert_details';

    // Set authoring information into sidebar.
    $form['author'] = [
      '#type' => 'details',
      '#title' => $this->t('Authoring information'),
      '#group' => 'advanced',
      '#weight' => 90,
      '#optional' => 1,
      '#open' => TRUE,
      '#attributes' => [
        'class' => ['entity-form-author'],
      ],
    ];

    // Move the authoring info into sidebar like nodes.
    $form['uid']['#group'] = 'author';
    $form['created']['#group'] = 'author';
    $form['revision_log_message']['#group'] = 'author';

    // Move new revision into author group.
    $form['new_revision']['#group'] = 'author';

    // Change the Title label.
    unset($form['title']['widget'][0]['value']['#description']);

    // Change the Link text description.
    $form['link']['widget'][0]['title']['#description'] = $this->t("If you don't write anything here, we will use: More information");

    $form['publishing_options'] = [
      '#type' => 'details',
      '#title' => $this->t('Publishing options.'),
      '#group' => 'advanced',
      '#weight' => 10,
      '#optional' => 1,
      '#open' => TRUE,
      '#attributes' => [
        'class' => ['entity-form-publishing'],
      ],
    ];

    // Move publishing options into sidebar like nodes.
    $form['display_title']['#group'] = 'publishing_options';
    $form['remove_hide_link']['#group'] = 'publishing_options';
    // Status.
    unset($form['status']);
    if ($entity->isNew()) {
      $status_message = $this->t('New %type', ['%type' => $entity->getEntityType()->getLabel()]);
    }
    elseif ($entity->isPublished()) {
      $status_message = $this->t('Live %type', ['%type' => $entity->getEntityType()->getLabel()]);
    }
    else {
      $status_message = $this->t('Stored %type', ['%type' => $entity->getEntityType()->getLabel()]);
    }
    $form['publishing_options']['status-message'] = [
      '#type' => 'markup',
      '#markup' => $status_message,
      '#weight' => -10,
    ];
    $form['publishing_options']['status-change'] = [
      '#type' => 'checkbox',
      '#title' => $entity->isPublished() ? $this->t('Remove') : $this->t('Set live'),
      '#description' => $this->t('On submission proceed to confirmation form to change the live status.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('new_revision') && $form_state->getValue('new_revision') != FALSE) {
      $entity->setNewRevision();

      // If a new revision is created, save the current user as revision author.
      $entity->setRevisionCreationTime($this->time->getRequestTime());
      $entity->setRevisionUserId($this->account->id());
    }
    else {
      $entity->setNewRevision(FALSE);
    }

    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Alert banner.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Alert banner.', [
          '%label' => $entity->label(),
        ]));
    }

    if ($form_state->getValue('status-change') == TRUE) {
      // Remove destination query and pass it through to the confirmation form.
      $destination = $this->request->getCurrentRequest()->query->get('destination');
      $this->request->getCurrentRequest()->query->remove('destination');
      $form_state->setRedirect('entity.localgov_alert_banner.status_form', ['localgov_alert_banner' => $entity->id()], ['query' => ['destination' => $destination]]);
    }
    else {
      $form_state->setRedirect('entity.localgov_alert_banner.collection', ['localgov_alert_banner' => $entity->id()]);
    }
  }

}
