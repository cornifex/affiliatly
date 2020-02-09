<?php

namespace Drupal\affiliatly\EventSubscriber;

use Drupal\commerce_order\Event\OrderEvent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\commerce_order\Event\OrderEvents;

/**
 * Class TrackPurchase.
 *
 * Tracks and updates purchases in Afiliatly.
 *
 * @package Drupal\affiliatly\EventSubscriber
 */
class TrackPurchase implements EventSubscriberInterface {

  const STATUS_UNPAID = 0;

  const STATUS_PAID = 1;

  /**
   * Affiliatly account code.
   *
   * @var string
   */
  private $code;

  /**
   * Affiliatly account hash.
   *
   * @var string
   */
  private $hash;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $config = $configFactory->get('affiliatly.settings');
    $this->hash = $config->get('hash');
    $this->code = $config->get('code');
    // todo: Bring the affialiatly API via another method. Library?
    $path = drupal_get_path('module', 'affiliatly');
    require_once $path . '/api_affiliatly.php';
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'commerce_order.place.post_transition' => ['push'],
      OrderEvents::ORDER_PAID => ['updateStatus'],
    ];
  }

  /**
   * Push data to Affiliatly when an order is paid.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The subscribed event.
   */
  public function push(WorkflowTransitionEvent $event) {
    if ($this->hash && $this->code) {
      $order = $event->getEntity();
      $price = $order->getTotalPrice();
      $price = number_format($price->getNumber(), 2);
      affiliatly_mark_purchase($this->code, $order->id(), $price, $this->hash);
      // Affiliatly automatically marks the order as paid, flag it as unpaid.
      affiliatly_order_status($this->code, $order->id(), self::STATUS_UNPAID, $this->hash);
    }
  }

  /**
   * Update the order status in Affiliatly when paid in full.
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The subscribed event.
   */
  public function updateStatus(OrderEvent $event) {
    if ($this->hash && $this->code) {
      $order = $event->getOrder();
      affiliatly_order_status($this->code, $order->id(), self::STATUS_PAID, $this->hash);
    }
  }

}
