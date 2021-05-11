<?php

namespace Drupal\localgov_alert_banner\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a Alert banner revision.
 *
 * @ingroup localgov_alert_banner
 */
class AlertBannerEntityRevisionDeleteForm extends ConfirmFormBase {

  /**
   * The Alert banner revision.
   *
   * @var \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface
   */
  protected $revision;

  /**
   * The Alert banner storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $alertBannerEntityStorage;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatInterface
   */
  protected $dateFormatter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->alertBannerEntityStorage = $container->get('entity_type.manager')->getStorage('localgov_alert_banner');
    $instance->connection = $container->get('database');
    $instance->dateFormatter = $container->get('date.formatter');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'localgov_alert_banner_revision_delete_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the revision from %revision-date?', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.localgov_alert_banner.version_history', ['localgov_alert_banner' => $this->revision->id()]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $localgov_alert_banner_revision = NULL) {
    $this->revision = $this->AlertBannerEntityStorage->loadRevision($localgov_alert_banner_revision);
    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->AlertBannerEntityStorage->deleteRevision($this->revision->getRevisionId());

    $this->logger('content')->notice('Alert banner: deleted %title revision %revision.', [
      '%title' => $this->revision->label(),
      '%revision' => $this->revision->getRevisionId(),
    ]);
    $this->messenger()->addMessage($this->t('Revision from %revision-date of Alert banner %title has been deleted.', [
      '%revision-date' => $this->dateFormatter->format($this->revision->getRevisionCreationTime()),
      '%title' => $this->revision->label(),
    ]));
    $form_state->setRedirect(
      'entity.localgov_alert_banner.canonical',
       ['localgov_alert_banner' => $this->revision->id()]
    );
    if ($this->connection->query('SELECT COUNT(DISTINCT vid) FROM {localgov_alert_banner_field_revision} WHERE id = :id', [':id' => $this->revision->id()])->fetchField() > 1) {
      $form_state->setRedirect(
        'entity.localgov_alert_banner.version_history',
         ['localgov_alert_banner' => $this->revision->id()]
      );
    }
  }

}
