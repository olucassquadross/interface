<?php
/*
 * Plugin Name: Endopay Gateway
 * Description: Custom payment gateway for WooCommerce
 * Version: 1.0
 * Author: Your Name
 */

// Make sure WooCommerce is active
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    function endopay_gateway_init() {
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

        class WC_Gateway_Endopay_Boleto extends WC_Payment_Gateway {
            public function __construct() {
                $this->id = 'endopay_boleto';
                $this->method_title = __( 'Endopay Boleto', 'woocommerce' );
                $this->method_description = __( 'Custom payment gateway for boleto payments.', 'woocommerce' );
                $this->has_fields = false;

                $this->init_form_fields();
                $this->init_settings();

                $this->title = $this->get_option( 'title' );
                $this->description = $this->get_option( 'description' );
                $this->debug = $this->get_option( 'debug' );

                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                add_action( 'woocommerce_thankyou', array( $this, 'display_boleto_iframe' ), 10, 1 );
                add_action( 'woocommerce_view_order', array( $this, 'display_boleto_iframe' ), 10, 1 );
            }

            public function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable/Disable', 'woocommerce' ),
                        'type' => 'checkbox',
                        'label' => __( 'Enable Endopay Boleto Payment', 'woocommerce' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'woocommerce' ),
                        'type' => 'text',
                        'default' => __( 'Boleto', 'woocommerce' )
                    ),
                    'description' => array(
                        'title' => __( 'Description', 'woocommerce' ),
                        'type' => 'textarea',
                        'default' => __( 'Pay with boleto.', 'woocommerce' )
                    ),
                    'debug' => array(
                        'title' => __( 'Debug Log', 'woocommerce' ),
                        'type' => 'checkbox',
                        'label' => __( 'Enable logging', 'woocommerce' ),
                        'default' => 'no',
                        'description' => __( 'Log events such as API requests', 'woocommerce' ),
                    ),
                );
            }

            public function process_payment( $order_id ) {
                $order = wc_get_order( $order_id );

                $response = $this->generate_boleto( $order );

                if ( $response && isset( $response->payment_method->url ) ) {
                    $order->update_status( 'on-hold', __( 'Awaiting boleto payment', 'woocommerce' ) );
                    wc_reduce_stock_levels( $order_id );
                    WC()->cart->empty_cart();

                    // Armazena a URL do boleto no meta do pedido
                    update_post_meta( $order_id, '_boleto_url', esc_url( $response->payment_method->url ) );

                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url( $order )
                    );
                } else {
                    wc_add_notice( 'Payment error: ' . $response->message, 'error' );
                    return;
                }
            }

            private function generate_boleto( $order ) {
                $order_data = $order->get_data();
                $customer_data = $order->get_address();
                $total = $order->get_total();

                $cpf = get_post_meta( $order->get_id(), '_billing_cpf', true );
                $birthdate = get_post_meta( $order->get_id(), '_billing_birthdate', true );

                $payload = array(
                    'documento_emissor' => '54349169000180',
                    'valor_em_centavos' => $total * 100,
                    'vencimento' => date('Y-m-d', strtotime('+7 days')),
                    'cliente' => array(
                        'documento' => $cpf,
                        'nome_cliente' => $order_data['billing']['first_name'] . ' ' . $order_data['billing']['last_name'],
                        'email' => $order_data['billing']['email'],
                        'celular' => $order_data['billing']['phone'],
                        'data_nascimento' => $birthdate,
                        'cep' => $customer_data['postcode'],
                        'uf' => $customer_data['state'],
                        'cidade' => $customer_data['city'],
                        'endereco' => $customer_data['address_1'],
                        'bairro' => $customer_data['address_2'],
                        'numero' => 'N/A',
                        'complemento' => ''
                    ),
                    'descricao' => 'Descrição da cobrança',
                    'reference_id' => $order->get_order_key(),
                    'url_logo' => 'https://zazpay.conectar.site/whitelabels/zaz/identidade_visual/logos/logo_principal.png',
                    'percentual_juros' => 2.00,
                    'percentual_multa' => 1.00,
                    'split' => array(
                        array(
                            'documento' => '17968083000100',
                            'valor_em_centavos' => 280
                        )
                    ),
                    'desconto' => array(
                        'tipo_desconto' => 'V',
                        'itens' => array(
                            array(
                                'data' => date('Y-m-d', strtotime('+1 days')),
                                'desconto_em_centavos' => 2000
                            ),
                            array(
                                'data' => date('Y-m-d', strtotime('+3 days')),
                                'desconto_em_centavos' => 1000
                            ),
                            array(
                                'data' => date('Y-m-d', strtotime('+7 days')),
                                'desconto_em_centavos' => 450
                            )
                        )
                    )
                );

                if ( $this->debug ) {
                    $this->log( 'Generating boleto with payload: ' . json_encode( $payload ) );
                }

                $response = wp_remote_post( 'https://reverb.conectar.site/api/index.php/api/venda/boleto', array(
                    'method'    => 'POST',
                    'body'      => json_encode( $payload ),
                    'headers'   => array(
                        'Content-Type'  => 'application/json',
                        'x-api-key'     => 'cee091f7a6b8635288e04854864f2bc92fb6a9b1',
                        'identifier'    => 'P_REVERBCD83F091B792F268C42D415C73E9B079572F4683'
                    ),
                ));

                if ( is_wp_error( $response ) ) {
                    if ( $this->debug ) {
                        $this->log( 'Error generating boleto: ' . $response->get_error_message() );
                    }
                    return false;
                }

                $body = wp_remote_retrieve_body( $response );
                $decoded_body = json_decode( $body );

                if ( $this->debug ) {
                    $this->log( 'Boleto generation response: ' . json_encode( $decoded_body ) );
                }

                return $decoded_body;
            }

            private function log( $message ) {
                if ( class_exists( 'WC_Logger' ) ) {
                    $logger = new WC_Logger();
                    $logger->add( 'endopay_boleto', $message );
                }
            }

            public function display_boleto_iframe( $order_id ) {
                $order = wc_get_order( $order_id );
                $boleto_url = get_post_meta( $order_id, '_boleto_url', true );

                if ( $order->get_payment_method() === 'endopay_boleto' && $boleto_url ) {
                    echo '<h2>' . __( 'Seu Boleto', 'woocommerce' ) . '</h2>';
                    echo '<iframe src="' . esc_url( $boleto_url ) . '" width="100%" height="600px" style="border: none;"></iframe>';
                }
            }
        }

        class WC_Gateway_Endopay_Credit_Card extends WC_Payment_Gateway {
            public function __construct() {
                $this->id = 'endopay_credit_card';
                $this->method_title = __( 'Endopay Credit Card', 'woocommerce' );
                $this->method_description = __( 'Custom payment gateway for credit card payments.', 'woocommerce' );
                $this->has_fields = true;

                $this->init_form_fields();
                $this->init_settings();

                $this->title = $this->get_option( 'title' );
                $this->description = $this->get_option( 'description' );
                $this->debug = $this->get_option( 'debug' );

                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            }

            public function init_form_fields() {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __( 'Enable/Disable', 'woocommerce' ),
                        'type' => 'checkbox',
                        'label' => __( 'Enable Endopay Credit Card Payment', 'woocommerce' ),
                        'default' => 'yes'
                    ),
                    'title' => array(
                        'title' => __( 'Title', 'woocommerce' ),
                        'type' => 'text',
                        'default' => __( 'Credit Card', 'woocommerce' )
                    ),
                    'description' => array(
                        'title' => __( 'Description', 'woocommerce' ),
                        'type' => 'textarea',
                        'default' => __( 'Pay with credit card.', 'woocommerce' )
                    ),
                    'debug' => array(
                        'title' => __( 'Debug Log', 'woocommerce' ),
                        'type' => 'checkbox',
                        'label' => __( 'Enable logging', 'woocommerce' ),
                        'default' => 'no',
                        'description' => __( 'Log events such as API requests', 'woocommerce' ),
                    ),
                );
            }

            public function payment_fields() {
                ?>
                <fieldset id="endopay-cc-form" class="wc-credit-card-form wc-payment-form">
                    <div class="form-row form-row-wide">
                        <label for="endopay-cc-name"><?php _e( 'Cardholder Name', 'woocommerce' ); ?> <span class="required">*</span></label>
                        <input id="endopay-cc-name" name="endopay_cc_name" type="text" autocomplete="cc-name" />
                    </div>
                    <div class="form-row form-row-wide">
                        <label for="endopay-cc-number"><?php _e( 'Card Number', 'woocommerce' ); ?> <span class="required">*</span></label>
                        <input id="endopay-cc-number" name="endopay_cc_number" type="text" autocomplete="cc-number" />
                    </div>
                    <div class="form-row form-row-first">
                        <label for="endopay-cc-expiry"><?php _e( 'Expiry Date', 'woocommerce' ); ?> <span class="required">*</span></label>
                        <input id="endopay-cc-expiry" name="endopay_cc_expiry" type="text" autocomplete="cc-exp" placeholder="MM / YY" />
                    </div>
                    <div class="form-row form-row-last">
                        <label for="endopay-cc-cvc"><?php _e( 'Card Code (CVC)', 'woocommerce' ); ?> <span class="required">*</span></label>
                        <input id="endopay-cc-cvc" name="endopay_cc_cvc" type="text" autocomplete="cc-csc" />
                    </div>
                    <div class="clear"></div>
                </fieldset>
                <?php
            }

            public function validate_fields() {
                if ( empty( $_POST['endopay_cc_name'] ) ) {
                    wc_add_notice( __( 'Cardholder Name is required.', 'woocommerce' ), 'error' );
                    return false;
                }
                if ( empty( $_POST['endopay_cc_number'] ) || ! ctype_digit( $_POST['endopay_cc_number'] ) ) {
                    wc_add_notice( __( 'Card Number is invalid.', 'woocommerce' ), 'error' );
                    return false;
                }
                if ( empty( $_POST['endopay_cc_expiry'] ) ) {
                    wc_add_notice( __( 'Expiry Date is required.', 'woocommerce' ), 'error' );
                    return false;
                }
                if ( empty( $_POST['endopay_cc_cvc'] ) || ! ctype_digit( $_POST['endopay_cc_cvc'] ) ) {
                    wc_add_notice( __( 'CVC is invalid.', 'woocommerce' ), 'error' );
                    return false;
                }
                return true;
            }

            public function process_payment( $order_id ) {
                if ( ! $this->validate_fields() ) {
                    return;
                }

                $order = wc_get_order( $order_id );
                $order->update_status( 'processing', __( 'Payment received', 'woocommerce' ) );
                wc_reduce_stock_levels( $order_id );
                WC()->cart->empty_cart();

                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url( $order )
                );
            }

            private function log( $message ) {
                if ( class_exists( 'WC_Logger' ) ) {
                    $logger = new WC_Logger();
                    $logger->add( 'endopay_credit_card', $message );
                }
            }
        }

        function add_endopay_gateway( $methods ) {
            $methods[] = 'WC_Gateway_Endopay_Boleto';
            $methods[] = 'WC_Gateway_Endopay_Credit_Card';
            return $methods;
        }

        add_filter( 'woocommerce_payment_gateways', 'add_endopay_gateway' );
    }

    add_action( 'plugins_loaded', 'endopay_gateway_init' );

    // Add custom fields to WooCommerce checkout
    add_action( 'woocommerce_after_order_notes', 'endopay_custom_checkout_fields' );

    function endopay_custom_checkout_fields( $checkout ) {
        echo '<div id="endopay_custom_checkout_fields"><h3>' . __('Informações Adicionais') . '</h3>';

        woocommerce_form_field( 'billing_cpf', array(
            'type'          => 'text',
            'class'         => array('billing-cpf form-row-wide'),
            'label'         => __('CPF'),
            'placeholder'   => __('Seu CPF'),
            'required'      => true,
        ), $checkout->get_value( 'billing_cpf' ));

        woocommerce_form_field( 'billing_birthdate', array(
            'type'          => 'date',
            'class'         => array('billing-birthdate form-row-wide'),
            'label'         => __('Data de Nascimento'),
            'placeholder'   => __('Sua data de nascimento'),
            'required'      => true,
        ), $checkout->get_value( 'billing_birthdate' ));

        echo '</div>';
    }

    // Save custom fields
    add_action( 'woocommerce_checkout_update_order_meta', 'endopay_save_custom_checkout_fields' );

    function endopay_save_custom_checkout_fields( $order_id ) {
        if ( ! empty( $_POST['billing_cpf'] ) ) {
            update_post_meta( $order_id, '_billing_cpf', sanitize_text_field( $_POST['billing_cpf'] ) );
        }
        if ( ! empty( $_POST['billing_birthdate'] ) ) {
            update_post_meta( $order_id, '_billing_birthdate', sanitize_text_field( $_POST['billing_birthdate'] ) );
        }
    }

    // Display custom fields in the admin order edit page
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'endopay_display_custom_checkout_fields_in_admin', 10, 1 );

    function endopay_display_custom_checkout_fields_in_admin( $order ) {
        echo '<p><strong>' . __('CPF') . ':</strong> ' . get_post_meta( $order->get_id(), '_billing_cpf', true ) . '</p>';
        echo '<p><strong>' . __('Data de Nascimento') . ':</strong> ' . get_post_meta( $order->get_id(), '_billing_birthdate', true ) . '</p>';
    }
}
?>
