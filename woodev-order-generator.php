<?php
/**
 * Plugin Name: Recurring Order Generator for Woo
 *
 * Description: Automatically generates sample WooCommerce orders daily for development purposes.
 * Version: 1.0.0
 * Author: Brian Coords
 * Author URI: https://briancoords.com
 * Requires at least: 6.0
 * Tested up to: 6.5
 * Requires PHP: 7.4
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woo-dev-order-generator
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 */
namespace WooDevOrderGenerator;

function generate_sample_orders(){
    if ( ! is_plugin_active( 'wc-smooth-generator/wc-smooth-generator.php' ) ) {
       return;
    }
    $remaining_amount = 20;
    $generated        = 0;

    while ( $remaining_amount > 0 ) {
        $batch = min( $remaining_amount, \WC\SmoothGenerator\Generator\Order::MAX_BATCH_SIZE );

        $result = \WC\SmoothGenerator\Generator\Order::batch( $batch, [] );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $generated        += count( $result );
        $remaining_amount -= $batch;
    }
}
add_action('woodev_generate_sample_daily', __NAMESPACE__ . '\\generate_sample_orders');


register_activation_hook(__FILE__, function() {
    if (class_exists('ActionScheduler')) {
        if (!as_next_scheduled_action('woodev_generate_sample_daily')) {
            as_schedule_recurring_action(time(), DAY_IN_SECONDS, 'woodev_generate_sample_daily');
        }
    }
});

register_deactivation_hook(__FILE__, function() {
    if (class_exists('ActionScheduler')) {
        as_unschedule_all_actions('woodev_generate_sample_daily');
    }
});