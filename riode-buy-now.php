<?php

/**
 * Plugin Name:       Riode Buy Now Button
 * Description:       Version of SBWC Buy Now button reworked for use with Riode.
 * Version:           1.0.0
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            WC Bessinger
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit();

add_action('plugins_loaded', function () {

    // button class
    require_once __DIR__ . '/class-riode-buy-now.php';
});
