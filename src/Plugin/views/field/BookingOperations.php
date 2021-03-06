<?php

/**
 * @file
 * Contains \Drupal\koba_booking\Plugin\views\field\BookingOperations.
 */

namespace Drupal\koba_booking\Plugin\views\field;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\views\Plugin\views\field\EntityOperations;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\koba_booking\Entity\Booking;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Renders all operations links for an entity.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("koba_booking_operations")
 */
class BookingOperations extends EntityOperations {
  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *    The entity manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $language_manager);
    $this->entityManager = $entity_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function defineOptions() {
    $options = parent::defineOptions();

    $options['destination'] = array(
      'default' => TRUE,
    );

    $options['allowedOperations'] = array(
      'default' => '',
    );

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['destination'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Include destination'),
      '#description' => $this->t('Include a <code>destination</code> parameter in the link to return the user to the original view upon completing the link action.'),
      '#default_value' => $this->options['destination'],
    );

    $form['allowedOperations'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Allowed booking operation'),
      '#options' => array(
        'edit' => $this->t('Edit'),
        'accepted' => $this->t('Accepted'),
        'refused' => $this->t('Refused'),
        'cancelled' => $this->t('Cancelled'),
        'confirmed' => $this->t('Confirmed'),
      ),
      '#description' => $this->t('The operation that can be performed in this view (if the user has the right permissions.)'),
      '#default_value' => $this->options['allowedOperations'],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);
    $operations = $this->entityManager->getListBuilder($entity->getEntityTypeId())->getOperations($entity);

    // Filter operations.
    if (!empty($this->options['allowedOperations'])) {
      $allowed = array_filter($this->options['allowedOperations']);
      // Check if the status is unconfirmed. If so, we allow manual retry.
      if (array_key_exists('confirmed', $allowed)) {
        $booking_status = $entity->booking_status->value;
        if ($booking_status != 'unconfirmed') {
          unset($allowed['confirmed']);
        }
      }
      foreach ($operations as $machinename => $operation) {
        // This could have been done with array_filter in PHP 5.6.
        if (!in_array($machinename, $allowed)) {
          unset($operations[$machinename]);
        }
      }
    }
    if ($this->options['destination']) {
      foreach ($operations as &$operation) {
        if (!isset($operation['query'])) {
          $operation['query'] = array();
        }
        $operation['query'] += $this->getDestinationArray();
      }
    }
    $build = array(
      '#type' => 'operations',
      '#links' => $operations,
    );

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // There is nothing to ensure or add for this handler, so we purposefully do
    //   nothing here and do not call parent::query() either.
  }
}
