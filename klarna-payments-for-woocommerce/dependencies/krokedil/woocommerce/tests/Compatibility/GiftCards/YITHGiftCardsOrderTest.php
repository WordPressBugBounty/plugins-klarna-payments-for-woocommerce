<?php

namespace KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce\Tests\Compatibility\GiftCards;

use KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce\Order\Order;
use WP_Mock\Tools\TestCase;
/**
 * Class YITHGiftCardsOrderTest
 *
 * The YITH gift card lines that the Order class builds on its own, outside the compatibility class.
 */
class YITHGiftCardsOrderTest extends TestCase
{
    /**
     * Init the test.
     *
     * @return void
     */
    public function setUp() : void
    {
        parent::setUp();
        // The Order class only reads YITH gift cards when YITH is active.
        if (!\class_exists('KrokedilKlarnaPaymentsDeps\\YITH_YWGC_Gift_Card')) {
            eval('class YITH_YWGC_Gift_Card {}');
            // phpcs:ignore Squiz.PHP.Eval.Discouraged -- Stands in for the YITH plugin.
        }
        \WP_Mock::userFunction('wp_parse_args', array('return' => array('slug' => 'test-slug', 'price_format' => 'minor')));
        \WP_Mock::userFunction('wc_format_decimal', array('return' => function ($price) {
            return $price;
        }));
        // No PW Gift Cards session.
        \WP_Mock::userFunction('WC', array('return' => new \stdClass()));
    }
    /**
     * Get the Order class for a mock order with no items and two YITH gift cards.
     *
     * Skips the constructor, which builds every other line and the customer too.
     *
     * @param string $updated_as_fee The value of YITH's flag for gift cards added as a fee.
     *
     * @return Order
     */
    private function get_order($updated_as_fee = '')
    {
        $mock_order = $this->getMockBuilder('WC_Order')->setMethods(array('get_items', 'get_meta'))->getMock();
        $mock_order->method('get_items')->willReturn(array());
        $mock_order->method('get_meta')->willReturnMap(array(array('_ywgc_applied_gift_cards', \true, array('coupon1' => 100, 'coupon2' => 200)), array('ywgc_gift_card_updated_as_fee', \true, $updated_as_fee)));
        $order = (new \ReflectionClass(Order::class))->newInstanceWithoutConstructor();
        // Only the base constructor, which sets the config the price formatting reads.
        (new \ReflectionMethod(\KrokedilKlarnaPaymentsDeps\Krokedil\WooCommerce\Base::class, '__construct'))->invoke($order, array('slug' => 'test-slug', 'price_format' => 'minor'));
        $order->order = $mock_order;
        return $order;
    }
    /**
     * Test that the order coupon lines include the YITH gift cards.
     *
     * @return void
     */
    public function test_line_coupons_include_yith_giftcards()
    {
        // Arrange.
        $order = $this->get_order();
        // Act
        $order->set_line_coupons();
        $actual = $order->get_line_coupons();
        // Assert
        $this->assertCount(2, $actual);
        $this->assertEquals('Gift card coupon1', $actual[0]->get_name());
        $this->assertEquals(-10000, $actual[0]->get_total_amount());
        $this->assertEquals('Gift card coupon2', $actual[1]->get_name());
        $this->assertEquals(-20000, $actual[1]->get_total_amount());
    }
    /**
     * Test that the order coupon lines skip the YITH gift cards once YITH has added them as a fee.
     *
     * @return void
     */
    public function test_line_coupons_skip_yith_giftcards_added_as_fee()
    {
        // Arrange.
        $order = $this->get_order('1');
        // Act
        $order->set_line_coupons();
        $actual = $order->get_line_coupons();
        // Assert
        $this->assertCount(0, $actual);
    }
}
