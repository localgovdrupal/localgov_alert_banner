<?php

namespace Drupal\localgov_alert_banner\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface;
use Drupal\views\Plugin\views\field\LinkBase;
use Drupal\views\ResultRow;

/**
 * Field handler to present link to un/publish confirmation page for an alert.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("localgov_alert_banner_status_page")
 */
class StatusPageLink extends LinkBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    unset($options['text']);
    $options['publish_text'] = ['default' => $this->getDefaultLabel(FALSE)];
    $options['unpublish_text'] = ['default' => $this->getDefaultLabel(TRUE)];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['publish_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Publish text to display'),
      '#description' => $this->t('Shown if the alert is not published and the option is to set it live.'),
      '#default_value' => $this->options['publish_text'],
    ];
    $form['unpublish_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Unpublish text to display'),
      '#description' => $this->t('Shown if the alert is published and the option is to remove the banner.'),
      '#default_value' => $this->options['unpublish_text'],
    ];
    parent::buildOptionsForm($form, $form_state);
    unset($form['text']);
  }

  /**
   * {@inheritdoc}
   */
  protected function getUrlInfo(ResultRow $row) {
    $alert_banner = $this->getEntity($row);
    assert($alert_banner instanceof AlertBannerEntityInterface);
    return $alert_banner->toUrl('status-form');
  }

  /**
   * {@inheritdoc}
   */
  protected function renderLink(ResultRow $row) {
    parent::renderLink($row);

    $alert_banner = $this->getEntity($row);
    assert($alert_banner instanceof AlertBannerEntityInterface);
    $this->options['alter']['query'] = $this->getDestinationArray();
    if ($alert_banner->isPublished()) {
      return !empty($this->options['unpublish_text']) ? $this->sanitizeValue($this->options['unpublish_text']) : $this->getDefaultLabel(TRUE);
    }
    else {
      return !empty($this->options['publish_text']) ? $this->sanitizeValue($this->options['publish_text']) : $this->getDefaultLabel(FALSE);
    }
  }

  /**
   * Returns the default label for this link.
   *
   * Overriden from the base by returning different values for published status.
   *
   * @param bool $published
   *   If the entity is published.
   *
   * @return string
   *   The default link label.
   */
  protected function getDefaultLabel($published = NULL) {
    if ($published === TRUE) {
      return $this->t('Remove banner');
    }
    elseif ($published === FALSE) {
      return $this->t('Set banner live');
    }
    return $this->t('Change status');
  }

}
