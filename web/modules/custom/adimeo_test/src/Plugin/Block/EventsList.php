<?php

namespace Drupal\adimeo_test\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block to display events list.
 *
 * @Block(
 *   id = "events_list_block",
 *   admin_label = @Translation("Events list"),
 * )
 */
class EventsList extends BlockBase implements BlockPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new EventsList instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity entity field manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Static method.
   *
   * @param array $container
   *   The container to pull out services used in the plugin.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $currentEvent = \Drupal::routeMatch()->getParameter('node');
    if ($currentEvent instanceof NodeInterface) {
      $eventTypeId = $currentEvent->get('field_event_type')->target_id;
      $nids = $this->getEventsIdsByType($eventTypeId, 3, $Eid = $currentEvent->id(), TRUE);
      if (($count = count($nids)) < 3) {
        $nidsOtherType = $this->getEventsIdsByType($eventTypeId, 3 - $count, $Eid, FALSE);
        $nids = array_merge($nids, $nidsOtherType);
      }
      $listEvents = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      // Build output for related events.
      $output = [
        '#theme' => 'item_list',
        '#items' => [],
        '#cache' => [
          'contexts' => ['url.path'],
          'tags' => ['node:' . $Eid, 'node_list:event'],
        ],
      ];

      foreach ($listEvents as $listEvent) {
        $output['#items'][] = [
          '#type' => 'markup',
          '#markup' => $listEvent->toLink()->toString(),
        ];
      }

      return $output;
    }
    return [];
  }

  /**
   * Get list events ids by type.
   * 
   * @param string $eventTypeId
   *   The type id of event.
   * @param string $nbr
   *   The end number of range.
   * @param string $current_nid
   *   The current event id.
   * @param bool $same_type
   *   Boolean for same event type or no.
   * 
   * @return array
   *   List of events ids.
   */
  public function getEventsIdsByType(string $eventTypeId, string $nbr, string $current_nid, bool $same_type): array {

    $currentDate = new DrupalDateTime();
    $formattedDate = $currentDate->format('Y-m-d\TH:i:s');
    $operator = "=";
    if (!$same_type) {
      $operator = "!=";
    }
    // Query events list.
    $query = $this->entityTypeManager->getStorage('node')->getQuery()
      ->condition('type', 'event')
      ->accessCheck(FALSE)
      ->condition('field_event_type', $eventTypeId, $operator)
      ->condition('field_date_range.end_value', $formattedDate, '>')
      ->condition('nid', $current_nid, '!=')
      ->sort('field_date_range.value', 'ASC')
      ->range(0, $nbr);

    return $query->execute();
  }

}
