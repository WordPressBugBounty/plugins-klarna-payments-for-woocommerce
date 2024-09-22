<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce\Compatibility\Giftcards;

use KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce\Compatibility\Abstracts\AbstractGiftCardCompatibility;
use KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce\OrderLineData;
/**
 * Class to handle compatibility with PW Gift Cards.
 */
class PWGiftCards extends AbstractGiftCardCompatibility
{
    /**
     * Get the giftcards applied to the current cart.
     *
     * @return OrderLineData[]
     */
    public function get_cart_giftcards()
    {
        $coupons = array();
        $pw_gift_card_data = WC()->session->get('pw-gift-card-data', array());
        if (!isset($pw_gift_card_data['gift_cards'])) {
            return $coupons;
        }
        foreach ($pw_gift_card_data['gift_cards'] as $code => $value) {
            $amount = $value * -1;
            $coupons[] = $this->create_gift_card("{$this->name} {$code}", $this->sku, $this->type, $amount);
        }
        return $coupons;
    }
    /**
     * Get the giftcards applied to an order.
     *
     * @param \WC_Order $order The WooCommerce order.
     *
     * @return OrderLineData[]
     */
    public function get_order_giftcards($order)
    {
        $coupons = array();
        /** $var WC_Order_Item_PW_Gift_Card $order_item */
        foreach ($order->get_items('pw_gift_card') as $id => $order_item) {
            $amount = $order_item->get_amount() * -1;
            $coupons[] = $this->create_gift_card("{$this->name} {$order_item->get_name()}", $this->sku, $this->type, $amount);
        }
        return $coupons;
    }
}
