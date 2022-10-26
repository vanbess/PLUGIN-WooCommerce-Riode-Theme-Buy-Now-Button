<?php
// Buy Now button class

if (!class_exists('Riode_Vans_Buy_Now')) :

    class Riode_Vans_Buy_Now {

        /**
         * Constructor
         */
        public function __construct() {

            // hook bn button to product single after default atc button
            add_action('woocommerce_after_add_to_cart_button', [__CLASS__, 'insert_button']);

            // hook scripts et al
            add_action('wp_head', [__CLASS__, 'buy_now_js_css']);

            // buy now ajax (simple/variable product)
            add_action('wp_ajax_nopriv_vans_riode_variable_buy_now', [__CLASS__, 'vans_riode_variable_buy_now']);
            add_action('wp_ajax_vans_riode_variable_buy_now', [__CLASS__, 'vans_riode_variable_buy_now']);
            add_action('wp_ajax_nopriv_vans_riode_simple_buy_now', [__CLASS__, 'vans_riode_simple_buy_now']);
            add_action('wp_ajax_vans_riode_simple_buy_now', [__CLASS__, 'vans_riode_simple_buy_now']);
        }

        /**
         * Insert buy now button
         */
        public function insert_button() {

            // bail if not simple or variable product
            global $post;

            $prod_type = wc_get_product($post->ID)->get_type();

            if ($prod_type == 'simple' || $prod_type == 'variable') :

                // display different button based on product type
                if ($prod_type == 'simple') : ?>
                    <button id="vans-riode-buy-now-btn-simple" class="product-buy-now button" style="min-width: 200px;"><?php _e('Buy Now!', 'woocommerce'); ?></button>
                <?php elseif ($prod_type == 'variable') : ?>
                    <button id="vans-riode-buy-now-btn-variable" class="product-buy-now button disabled wc-variation-selection-needed" style="min-width: 200px;">
                        <?php _e('Buy Now!', 'woocommerce'); ?>
                    </button>
                <?php endif;

            endif;
        }

        /**
         * Button JS to add to cart and sort some other issues
         */
        public function buy_now_js_css() {

            if (is_product()) :

                global $post;
                $prod_type = wc_get_product($post->ID)->get_type();

                // setup variation error message
                $err_msg = __('Please select some product options before adding this product to your basket.', 'woocommerce');

                // only run js/css for valid product types
                if ($prod_type == 'variable' || $prod_type == 'simple') : ?>

                    <!-- js -->
                    <script id="vans-riode-buy-now-btn-js" data-product-type="<?php echo $prod_type; ?>" data-var-err-msg="<?php echo $err_msg; ?>">
                        'use strict';

                        $ = jQuery;

                        $(document).ready(function() {

                            // If variation pre-selected on page load
                            if ($('.variation_id').val() !== '') {
                                $('#vans-riode-buy-now-btn-variable').removeClass('disabled wc-variation-selection-needed');
                            }

                            // Disable/enable variable buy now on variation id change
                            $('.variation_id').change(function(e) {

                                if ($(this).val() === '') {
                                    $('#vans-riode-buy-now-btn-variable').addClass('disabled wc-variation-selection-needed');
                                } else {
                                    $('#vans-riode-buy-now-btn-variable').removeClass('disabled wc-variation-selection-needed');
                                }

                            });

                            // ~~~~~~~~~~~~~~~~~~~~~~~~~~
                            // Variable buy now on click
                            // ~~~~~~~~~~~~~~~~~~~~~~~~~~
                            $('#vans-riode-buy-now-btn-variable').click(function(e) {

                                e.preventDefault();

                                // setup vars
                                var var_id = $('.variation_id').val(),
                                    parent_id = $('input[name=product_id]').val(),
                                    qty = $('.qty').val(),
                                    v_upsells = [],
                                    s_upsells = [];

                                // get selected variable upsells
                                $('.upsell-v2-product-upsell-variable-prod-cb').each(function() {

                                    if ($(this).is(':checked')) {
                                        v_upsells.push({
                                            'qty': $(this).attr('data-qty'),
                                            'var_id': $(this).attr('data-variation-id'),
                                            'parent_id': $(this).attr('data-parent-id')
                                        });
                                    }

                                });

                                // get selected simple upsells
                                $('.upsell-v2-product-upsell-simple-prod-cb').each(function() {

                                    if ($(this).is(':checked')) {
                                        s_upsells.push({
                                            'qty': $(this).attr('data-qty'),
                                            'prod_id': $(this).attr('data-product-id'),
                                        });
                                    }

                                });

                                // send ajax request to add to cart
                                var ajaxurl = '<?php echo admin_url('admin-ajax.php') ?>';

                                var data = {
                                    'action': 'vans_riode_variable_buy_now',
                                    '_ajax_nonce': '<?php echo wp_create_nonce('vans riode variable buy now') ?>',
                                    'var_id': var_id,
                                    'parent_id': parent_id,
                                    'qty': qty,
                                    'v_upsells': v_upsells,
                                    's_upsells': s_upsells
                                };

                                $.post(ajaxurl, data, function(response) {
                                    if (response.length > 0) {
                                        location.replace('checkout');
                                    }
                                });

                            });

                            // ~~~~~~~~~~~~~~~~~~~~~~~~
                            // Simple buy now on click
                            // ~~~~~~~~~~~~~~~~~~~~~~~~
                            $('#vans-riode-buy-now-btn-simple').click(function(e) {
                                e.preventDefault();

                                // setup vars
                                var product_id = $('input[name=product_id]').val(),
                                    qty = $('.qty').val(),
                                    v_upsells = [],
                                    s_upsells = [];

                                // get selected variable upsells
                                $('.upsell-v2-product-upsell-variable-prod-cb').each(function() {

                                    if ($(this).is(':checked')) {
                                        v_upsells.push({
                                            'qty': $(this).attr('data-qty'),
                                            'var_id': $(this).attr('data-variation-id'),
                                            'parent_id': $(this).attr('data-parent-id')
                                        });
                                    }

                                });

                                // get selected simple upsells
                                $('.upsell-v2-product-upsell-simple-prod-cb').each(function() {

                                    if ($(this).is(':checked')) {
                                        s_upsells.push({
                                            'qty': $(this).attr('data-qty'),
                                            'prod_id': $(this).attr('data-product-id'),
                                        });
                                    }

                                });

                                // send ajax request to add to cart
                                var ajaxurl = '<?php echo admin_url('admin-ajax.php') ?>';

                                var data = {
                                    'action': 'vans_riode_simple_buy_now',
                                    '_ajax_nonce': '<?php echo wp_create_nonce('vans riode simple buy now') ?>',
                                    'product_id': product_id,
                                    'qty': qty,
                                    'v_upsells': v_upsells,
                                    's_upsells': s_upsells
                                };

                                $.post(ajaxurl, data, function(response) {
                                    console.log(response);
                                });

                            });

                        });
                    </script>

<?php endif;
            endif;
        }

        /**
         * AJAX to add to cart variable
         */
        public function vans_riode_variable_buy_now() {

            check_ajax_referer('vans riode variable buy now');

            $atc_data = [];

            // add main prod to cart
            $atc_data = wc()->cart->add_to_cart($_POST['parent_id'], $_POST['qty'], $_POST['parent_id']);

            // add variable upsells to cart
            if (!empty($_POST['v_upsells'])) :

                // loop
                foreach ($_POST['v_upsells'] as $v_upsell) :
                    $atc_data[] = wc()->cart->add_to_cart($v_upsell['parent_id'], $v_upsell['qty'], $v_upsell['var_id']);
                endforeach;

            endif;

            // add simple upsells to cart
            if (!empty($_POST['s_upsells'])) :

                // loop
                foreach ($_POST['s_upsells'] as $s_upsell) :
                    $atc_data[] = wc()->cart->add_to_cart($s_upsell['prod_id'], $s_upsell['qty']);
                endforeach;

            endif;

            wp_send_json($atc_data);

            wp_die();
        }

        /**
         * AJAX to add to cart simple
         */
        public function vans_riode_simple_buy_now() {

            check_ajax_referer('vans riode simple buy now');

            $atc_data = [];

            // add main prod to cart
            $atc_data = wc()->cart->add_to_cart($_POST['product_id'], $_POST['qty']);

            // add variable upsells to cart
            if (!empty($_POST['v_upsells'])) :

                // loop
                foreach ($_POST['v_upsells'] as $v_upsell) :
                    $atc_data[] = wc()->cart->add_to_cart($v_upsell['parent_id'], $v_upsell['qty'], $v_upsell['var_id']);
                endforeach;

            endif;

            // add simple upsells to cart
            if (!empty($_POST['s_upsells'])) :

                // loop
                foreach ($_POST['s_upsells'] as $s_upsell) :
                    $atc_data[] = wc()->cart->add_to_cart($s_upsell['prod_id'], $s_upsell['qty']);
                endforeach;

            endif;

            wp_send_json($atc_data);

            wp_die();
        }
    }

    new Riode_Vans_Buy_Now;

endif;

?>