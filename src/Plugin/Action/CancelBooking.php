<?php

/**
 * @file
 * Contains \Drupal\koba_booking\Plugin\Action\CancelBooking.
 */

namespace Drupal\koba_booking\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\koba_booking\Exception\ProxyException;

/**
 * Cancel booking.
 *
 * @Action(
 *   id = "koba_booking_cancel_action",
 *   label = @Translation("Cancel the selected booking(s)"),
 *   type = "koba_booking_booking"
 * )
 */
class CancelBooking extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($booking = NULL) {
    // Get proxy service.
    $proxy = \Drupal::service('koba_booking.api.proxy');

    try {
      if ($proxy->deleteBooking($booking)) {
        // For efficiency manually save the original booking before applying any
        // changes.
        $booking->original = clone $booking;
        $booking->set('booking_status', 'pending');
        $booking->save();
      }
    }
    catch (ProxyException $exception) {
      drupal_set_message(t($exception->getMessage()), 'error');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\user\UserInterface $object */
    $access = $object->access('edit status accepted', $account, TRUE);

    return $return_as_object ? $access : $access->isAllowed();
  }
}
