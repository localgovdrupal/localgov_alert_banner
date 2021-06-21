<?php

namespace Drupal\localgov_alert_banner\Entity;

use Drupal\condition_field\ConditionAccessResolver;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Alert banner entity.
 *
 * @ingroup localgov_alert_banner
 *
 * @ContentEntityType(
 *   id = "localgov_alert_banner",
 *   label = @Translation("Alert banner"),
 *   bundle_label = @Translation("Alert banner type"),
 *   handlers = {
 *     "storage" = "Drupal\localgov_alert_banner\AlertBannerEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\localgov_alert_banner\AlertBannerEntityListBuilder",
 *     "views_data" = "Drupal\localgov_alert_banner\Entity\AlertBannerEntityViewsData",
 *     "translation" = "Drupal\localgov_alert_banner\AlertBannerEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\localgov_alert_banner\Form\AlertBannerEntityForm",
 *       "add" = "Drupal\localgov_alert_banner\Form\AlertBannerEntityForm",
 *       "edit" = "Drupal\localgov_alert_banner\Form\AlertBannerEntityForm",
 *       "delete" = "Drupal\localgov_alert_banner\Form\AlertBannerEntityDeleteForm",
 *       "status" = "Drupal\localgov_alert_banner\Form\AlertBannerEntityStatusForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\localgov_alert_banner\AlertBannerEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\localgov_alert_banner\AlertBannerEntityAccessControlHandler",
 *   },
 *   base_table = "localgov_alert_banner",
 *   data_table = "localgov_alert_banner_field_data",
 *   revision_table = "localgov_alert_banner_revision",
 *   revision_data_table = "localgov_alert_banner_field_revision",
 *   translatable = TRUE,
 *   permission_granularity = "bundle",
 *   admin_permission = "manage all localgov alert banner entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_uid",
 *     "revision_created" = "revision_timestamp",
 *     "revision_log_message" = "revision_log",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/alert-banner/{localgov_alert_banner}",
 *     "add-page" = "/admin/content/alert-banner/add",
 *     "add-form" = "/admin/content/alert-banner/add/{localgov_alert_banner_type}",
 *     "edit-form" = "/admin/content/alert-banner/{localgov_alert_banner}/edit",
 *     "delete-form" = "/admin/content/alert-banner/{localgov_alert_banner}/delete",
 *     "status-form" = "/admin/content/alert-banner/{localgov_alert_banner}/status",
 *     "version-history" = "/admin/content/alert-banner/{localgov_alert_banner}/revisions",
 *     "revision" = "/admin/content/alert-banner/{localgov_alert_banner}/revisions/{localgov_alert_banner_revision}/view",
 *     "revision_revert" = "/admin/content/alert-banner/{localgov_alert_banner}/revisions/{localgov_alert_banner_revision}/revert",
 *     "revision_delete" = "/admin/content/alert-banner/{localgov_alert_banner}/revisions/{localgov_alert_banner_revision}/delete",
 *     "translation_revert" = "/admin/content/alert-banner/{localgov_alert_banner}/revisions/{localgov_alert_banner_revision}/revert/{langcode}",
 *     "collection" = "/admin/content/alert-banner",
 *   },
 *   bundle_entity_type = "localgov_alert_banner_type",
 *   field_ui_base_route = "entity.localgov_alert_banner_type.edit_form"
 * )
 */
class AlertBannerEntity extends EditorialContentEntityBase implements AlertBannerEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * Drupal\Core\Condition\ConditionManager definition.
   *
   * @var \Drupal\Core\Condition\ConditionManager
   */
  protected $pluginManagerCondition;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type, $bundle = FALSE, $translations = []) {
    parent::__construct($values, $entity_type, $bundle, $translations);
    $this->pluginManagerCondition = \Drupal::service('plugin.manager.condition');
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the localgov_alert_banner owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }

    // Regenerate a JS token for the updated alert banner.
    if ($this->get('status')->value) {
      $prefix = 'alert-' . $this->id() . '-';
      $hash = sha1(uniqid('', TRUE));
      $this->setToken($prefix . '-' . $hash);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Better to use cache tags instead of doing a full flush?
    drupal_flush_all_caches();
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setTitle($title) {
    $this->set('title', $title);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getToken() {
    return $this->get('token')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setToken($token) {
    $this->set('token', $token);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * Is the alert banner visible?
   *
   * @return bool
   *   True if the alert banner is visible, otherwise FALSE.
   */
  public function isVisible() {

    // Check if the field is present and has a value.
    if (!$this->hasField('visibility') || $this->get('visibility')->isEmpty()) {
      return TRUE;
    }

    // Visibility condition config.
    $conditions_config = $this->get('visibility')->getValue()[0]['conditions'];

    // Construct visibility conditions.
    $conditions = [];
    foreach ($conditions_config as $condition_id => $values) {
      /** @var \Drupal\Core\Condition\ConditionInterface $condition */
      $condition = $this->pluginManagerCondition->createInstance($condition_id, $values);
      $conditions[] = $condition;
    }

    // Check if visibility conditions met.
    if (ConditionAccessResolver::checkAccess($conditions, 'or')) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Alert banner entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of the Alert banner.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Remove hide link.
    $fields['display_title'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Display title'))
      ->setDescription(t('Show the title on the alert banner.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);

    // Remove hide link.
    $fields['remove_hide_link'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Remove hide link'))
      ->setDescription(t('This will remove the hide link that appears on alert banners.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(0)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', FALSE);

    $fields['status']->setDescription(t('A boolean indicating whether the Alert banner is published.'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    // Token used for the cookie when the banner is hidden.
    $fields['token'] = BaseFieldDefinition::create('string')
      ->setSetting('max_length', 64)
      ->setDisplayConfigurable('form', FALSE)
      ->setDisplayConfigurable('view', FALSE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {

    // Add cache contexts depending on the enabled visibility condition plugins.
    if ($this->hasField('visibility') && !$this->get('visibility')->isEmpty()) {
      $contexts = [];
      $conditions_config = $this->get('visibility')->getValue()[0]['conditions'];

      foreach ($conditions_config as $condition_id => $values) {
        /** @var \Drupal\Core\Condition\ConditionInterface $condition */
        $condition = $this->pluginManagerCondition->createInstance($condition_id, $values);
        $contexts = Cache::mergeContexts($contexts, $condition->getCacheContexts());
      }

      $this->addCacheContexts($contexts);
    }

    return parent::getCacheContexts();
  }

}
