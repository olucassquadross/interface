<?php

class WC_Endopay_Gateway_Blocks_Support {
    public function __construct() {
        add_action( 'woocommerce_blocks_payment_method_type_registration', array( $this, 'register_payment_method' ) );
    }

    public function register_payment_method( $payment_method_registry ) {
        $payment_method_registry->register(
            'endopay_boleto',
            array(
                'title'       => __( 'Boleto', 'woocommerce' ),
                'description' => __( 'Pay with boleto.', 'woocommerce' ),
                'supports'    => array( 'products' ),
                'content'     => $this->get_content(),
            )
        );

        $payment_method_registry->register(
            'endopay_credit_card',
            array(
                'title'       => __( 'Credit Card', 'woocommerce' ),
                'description' => __( 'Pay with credit card.', 'woocommerce' ),
                'supports'    => array( 'products' ),
                'content'     => $this->get_content(),
            )
        );
    }

    protected function get_content() {
        return '<p>' . __( 'Payment method content goes here.', 'woocommerce' ) . '</p>';
    }
}

new WC_Endopay_Gateway_Blocks_Support();

?>
