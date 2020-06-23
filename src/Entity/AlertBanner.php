<?php

namespace Drupal\localgov_alert_banner\Entity;

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
 *     "storage" = "Drupal\localgov_alert_banner\AlertBannerStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\localgov_alert_banner\AlertBannerListBuilder",
 *     "views_data" = "Drupal\localgov_alert_banner\Entity\AlertBannerViewsData",
 *     "translation" = "Drupal\localgov_alert_banner\AlertBannerTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\localgov_alert_banner\Form\AlertBannerForm",
 *       "add" = "Drupal\localgov_alert_banner\Form\AlertBannerForm",
 *       "edit" = "Drupal\localgov_alert_banner\Form\AlertBannerForm",
 *       "delete" = "Drupal\localgov_alert_banner\Form\AlertBannerDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\localgov_alert_banner\AlertBannerHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\localgov_alert_banner\AlertBannerAccessControlHandler",
 *   },
 *   base_table = "localgov_alert_banner",
 *   data_table = "localgov_alert_banner_field_data",
 *   revision_table = "localgov_alert_banner_revision",
 *   revision_data_table = "localgov_alert_banner_field_revision",
 *   translatable = TRUE,
 *   admin_permission = "administer alert banner entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/admin/content/localgov_alert_banner/{localgov_alert_banner}",
 *     "add-page" = "/admin/content/localgov_alert_banner/add",
 *     "add-form" = "/admin/content/localgov_alert_banner/add/{localgov_alert_banner_type}",
 *     "edit-form" = "/admin/content/localgov_alert_banner/{localgov_alert_banner}/edit",
 *     "delete-form" = "/admin/content/localgov_alert_banner/{localgov_alert_banner}/delete",
 *     "version-history" = "/admin/content/localgov_alert_banner/{localgov_alert_banner}/revisions",
 *     "revision" = "/admin/content/localgov_alert_banner/{localgov_alert_banner}/revisions/{localgov_alert_banner_revision}/view",
 *     "revision_revert" = "/admin/content/localgov_alert_banner/{localgov_alert_banner}/revisions/{localgov_alert_banner_revision}/revert",
 *     "revision_delete" = "/admin/content/localgov_alert_banner/{localgov_alert_banner}/revisions/{localgov_alert_banner_revision}/delete",
 *     "translation_revert" = "/admin/content/localgov_alert_banner/{localgov_alert_banner}/revisions/{localgov_alert_banner_revision}/revert/{langcode}",
 *     "collection" = "/admin/content/localgov_alert_banner",
 *   },
 *   bundle_entity_type = "localgov_alert_banner_type",
 * )
 */
class AlertBanner extends EditorialContentEntityBase implements AlertBannerInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
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
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('title')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('title', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    $storage = $this->entityTypeManager()->getStorage('localgov_alert_banner_type');
    $alert_banner_type = $storage->load($this->get('type')->target_id);
    return $alert_banner_type->label();
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
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Author.
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The username of the author.'))
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

    // Banner type.
    $fields['alert_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type of alert'))
      ->setRevisionable(TRUE)
      ->setDefaultValue('')
      ->setSettings([
        'allowed_values' => [
          'minor' => 'Minor alert',
          'major' => 'Emergency',
          'notable-person' => 'Death of a notable person',
        ],
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'list_string',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    // Banner title.
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

    // Banner short description.
    $fields['short_description'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Short description'))
      ->setDescription(t('No more than 50 or so characters'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 100,
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
      ->setDisplayConfigurable('view', TRUE);

    // Banner link.
    $fields['link'] = BaseFieldDefinition::create('link')
      ->setLabel(t('Link'))
      ->setRevisionable(TRUE)
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
      ->setDisplayConfigurable('view', TRUE);

    // Remove hide link.
    $fields['remove_hide_link'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Remove hide link'))
      ->setDescription(t('This will remove the hide link that appears on alert banners.'))
      ->setRevisionable(TRUE)
      ->setDefaultValue(0)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'checkbox',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    // Created.
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the banner was created.'));

    // Changed.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the banner was last edited.'));

    // Revision translation.
    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
