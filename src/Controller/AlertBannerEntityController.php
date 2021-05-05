<?php

namespace Drupal\localgov_alert_banner\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AlertBannerEntityController.
 *
 *  Returns responses for Alert banner routes.
 */
class AlertBannerEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Alert banner revision.
   *
   * @param int $localgov_alert_banner_revision
   *   The Alert banner revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($localgov_alert_banner_revision) {
    $localgov_alert_banner = $this->entityTypeManager()->getStorage('localgov_alert_banner')
      ->loadRevision($localgov_alert_banner_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('localgov_alert_banner');

    return $view_builder->view($localgov_alert_banner);
  }

  /**
   * Page title callback for a Alert banner revision.
   *
   * @param int $localgov_alert_banner_revision
   *   The Alert banner revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($localgov_alert_banner_revision) {
    $localgov_alert_banner = $this->entityTypeManager()->getStorage('localgov_alert_banner')
      ->loadRevision($localgov_alert_banner_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $localgov_alert_banner->label(),
      '%date' => $this->dateFormatter->format($localgov_alert_banner->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of an Alert banner.
   *
   * @param \Drupal\localgov_alert_banner\Entity\AlertBannerEntityInterface $localgov_alert_banner
   *   A Alert banner object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AlertBannerEntityInterface $localgov_alert_banner) {
    $account = $this->currentUser();
    $localgov_alert_banner_storage = $this->entityTypeManager()->getStorage('localgov_alert_banner');

    $langcode = $localgov_alert_banner->language()->getId();
    $langname = $localgov_alert_banner->language()->getName();
    $languages = $localgov_alert_banner->getTranslationLanguages();
    $type_id = $localgov_alert_banner->bundle();
    $has_translations = (count($languages) > 1);
    if ($has_translations) {
      $build['#title'] = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $localgov_alert_banner->label(),
      ]);
    }
    else {
      $build['#title'] = $this->t('Revisions for %title', ['%title' => $localgov_alert_banner->label()]);
    }
    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("manage all localgov alert banner entities") || $account->hasPermission('manage localgov alert banner ' . $type_id . ' entities')));
    $delete_permission = (($account->hasPermission("manage all localgov alert banner entities") || $account->hasPermission('manage localgov alert banner ' . $type_id . ' entities')));

    $rows = [];

    $vids = $localgov_alert_banner_storage->revisionIds($localgov_alert_banner);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\localgov_alert_banner\AlertBannerEntityInterface $revision */
      $revision = $localgov_alert_banner_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $localgov_alert_banner->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.localgov_alert_banner.revision', [
            'localgov_alert_banner' => $localgov_alert_banner->id(),
            'localgov_alert_banner_revision' => $vid,
          ]));
        }
        else {
          $link = $localgov_alert_banner->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.localgov_alert_banner.translation_revert', [
                'localgov_alert_banner' => $localgov_alert_banner->id(),
                'localgov_alert_banner_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.localgov_alert_banner.revision_revert', [
                'localgov_alert_banner' => $localgov_alert_banner->id(),
                'localgov_alert_banner_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.localgov_alert_banner.revision_delete', [
                'localgov_alert_banner' => $localgov_alert_banner->id(),
                'localgov_alert_banner_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['localgov_alert_banner_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * Publish or Unpublish title for alert banner status change form.
   */
  public function getStatusFormTitle(AlertBannerEntityInterface $localgov_alert_banner) {
    return $localgov_alert_banner->isPublished() ? $this->t('Remove banner') : $this->t('Put banner live');
  }

}
