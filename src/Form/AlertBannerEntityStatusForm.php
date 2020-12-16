<?php

namespace Drupal\localgov_alert_banner\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntity;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface;

/**
 * Provides a alert banner entity un/publish form.
 */
class AlertBannerEntityStatusForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $entity = $this->getEntity();
    assert($entity instanceof AlertBannerEntityInterface);

    if ($entity->isPublished()) {
      $form['description'] = [
        '#markup' => $this->t('Remove current @entity-type %label?', [
          '@entity-type' => $this->getEntity()->getEntityType()->getSingularLabel(),
          '%label' => $this->getEntity()->label(),
        ]),
      ];
    }
    else {
      $form['description'] = [
        'introduction' => [
          '#markup' => $this->t('Set the following @entity-type live?', [
            '@entity-type' => $this->getEntity()->getEntityType()->getSingularLabel(),
          ]),
        ],
        'entity' => $this->entityTypeManager
          ->getViewBuilder($entity->getEntityTypeId())
          ->view($entity),
      ];

      // List of currently published alerts. Should only ever be one.
      $published_titles = [];
      $entity_query = $this->entityTypeManager
        ->getStorage($entity->getEntityTypeId())
        ->getQuery();
      $entity_query->accessCheck(FALSE);
      $entity_query->condition('status', TRUE);
      $published_entities = $entity_query->execute();
      if (!empty($published_entities)) {
        foreach ($published_entities as $published) {
          $alert = AlertBannerEntity::load($published);
          $published_titles[] = $alert->label();
        }
      }

      // Add the unpublish other banners checkbox.
      $form['unpublish_others'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Unpublish all other alerts.'),
        '#default_value' => 1,
      ];

      if (!empty($published_titles)) {
        $form['unpublish_others']['#description'] = $this->t('This will also remove the banner %title', [
          '%title' => implode(', ', $published_titles),
        ]);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    assert($entity instanceof AlertBannerEntityInterface);

    $entity->status->value = !$entity->status->value;
    $entity->save();
    $this->setEntity($entity);

    $message = $this->getStatusChangedMessage();
    $this->messenger()->addStatus($message);
    $this->logStatusChanged();

    // If the unpublish checkbox set, unplublish other banners.
    if ($form_state->hasValue('unpublish_others') && $form_state->getValue('unpublish_others') === 1) {
      $entity_query = $this->entityTypeManager
        ->getStorage($entity->getEntityTypeId())
        ->getQuery();
      $entity_query->accessCheck(FALSE);
      $entity_query->condition('status', TRUE);
      $entity_query->condition('id', $entity->id(), '<>');
      $published_entities = $entity_query->execute();
      if (!empty($published_entities)) {
        foreach ($published_entities as $published) {
          $current = AlertBannerEntity::load($published);
          $current->set('status', FALSE);
          $current->save();
        }
      }
    }

    $form_state->setRedirect('entity.localgov_alert_banner.collection', ['localgov_alert_banner' => $entity->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $entity = $this->getEntity();
    assert($entity instanceof AlertBannerEntityInterface);
    return $entity->toUrl('canonical');
  }

  /**
   * {@inheritdoc}
   */
  protected function getStatusChangedMessage() {
    $entity = $this->getEntity();
    assert($entity instanceof AlertBannerEntityInterface);

    return $entity->isPublished() ?
      $this->t('The @entity-type %label has been set live.', [
        '@entity-type' => $entity->getEntityType()->getSingularLabel(),
        '%label'       => $entity->label(),
      ]) :
      $this->t('The @entity-type %label has been removed.', [
        '@entity-type' => $entity->getEntityType()->getSingularLabel(),
        '%label'       => $entity->label(),
      ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function logStatusChanged() {
    $entity = $this->getEntity();
    assert($entity instanceof AlertBannerEntityInterface);

    $this->logger($entity->getEntityType()->getProvider())->notice('The @entity-type %label has been %published.', [
      '@entity-type' => $entity->getEntityType()->getSingularLabel(),
      '%label'       => $entity->label(),
      '%published'   => $entity->isPublished() ? $this->t('published') : $this->t('unpublished'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $entity = $this->getEntity();
    assert($entity instanceof AlertBannerEntityInterface);

    return $entity->isPublished() ?
      $this->t('Are you sure you want to remove @entity-type %label?', [
        '@entity-type' => $this->getEntity()->getEntityType()->getSingularLabel(),
        '%label' => $this->getEntity()->label(),
      ]) :
      $this->t('Are you sure you want to set @entity-type %label live?', [
        '@entity-type' => $this->getEntity()->getEntityType()->getSingularLabel(),
        '%label' => $this->getEntity()->label(),
      ]);
  }

}
