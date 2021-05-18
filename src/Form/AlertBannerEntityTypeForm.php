<?php

namespace Drupal\localgov_alert_banner\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for edting alert banner types.
 */
class AlertBannerEntityTypeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $localgov_alert_banner_type = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $localgov_alert_banner_type->label(),
      '#description' => $this->t("Label for the Alert banner type."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $localgov_alert_banner_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\localgov_alert_banner\Entity\AlertBannerEntityType::load',
      ],
      '#disabled' => !$localgov_alert_banner_type->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $localgov_alert_banner_type = $this->entity;
    $status = $localgov_alert_banner_type->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Alert banner type.', [
          '%label' => $localgov_alert_banner_type->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Alert banner type.', [
          '%label' => $localgov_alert_banner_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($localgov_alert_banner_type->toUrl('collection'));
  }

}
