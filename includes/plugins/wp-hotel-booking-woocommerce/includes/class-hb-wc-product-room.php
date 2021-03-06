<?php

if ( !class_exists( 'WC_Product_Simple' ) )
	return;


global $woocommerce;

if ( $woocommerce && version_compare( $woocommerce->version, '3.0.0', '<' ) ) {

	require_once 'class-hb-wc-2x-product-room.php';
	return;

} else {

	class HB_WC_Product_Room extends WC_Product_Simple {

		//public $data = null;
		public $total;

		/*
		 * Room product data
		 */

		/**
		 * HB_WC_Product_Room constructor
		 *
		 * @param mixed $product
		 */
		public function __construct( $product = 0 ) {
			// Should not call constructor of parent
			//parent::__construct( $product );
			if ( is_numeric( $product ) && $product > 0 ) {
				$this->set_id( $product );
			} elseif ( $product instanceof self ) {
				$this->set_id( absint( $product->get_id() ) );
			} elseif ( !empty( $product->ID ) ) {
				$this->set_id( absint( $product->ID ) );
			}
		}

		function get_price( $context = 'view' ) {
			//$room = WPHB_Room::instance( $this->post, $this->data );
			$room = WPHB_Room::instance( $this->get_id(), $this->get_data() );

			return $room->amount_singular_exclude_tax;
		}

		/**
		 * Check if a product is purchasable
		 */
		function is_purchasable( $context = 'view' ) {
			return true;
		}


		public function get_stock_status( $context = 'view' ) {
			return $this->get_stock_quantity( $context ) > 0 ? 'instock' : '';
		}

		/**
		 * @param string $context
		 *
		 * @return bool
		 */
		public function exists( $context = 'view' ) {
			return $this->get_id() && ( get_post_type( $this->get_id() ) == 'hb_room' ) && ( !in_array( get_post_status( $this->get_id() ), array( 'draft', 'auto-draft' ) ) );
		}

		public function is_virtual() {
			return true;
		}

		/**
		 * @param string $context
		 *
		 * @return string
		 */
		public function get_name( $context = 'view' ) {
			return get_the_title( $this->get_id() );
		}

		public function is_in_stock() {
			return true;
		}

		public function set_check_in_date( $value ) {
			$this->data['check_in_date'] = $value;
		}

		public function set_check_out_date( $value ) {
			$this->data['check_out_date'] = $value;
		}

		public function set_parent_id( $value ) {
			$this->data['parent_id'] = $value;
		}

		public function set_product_id( $value ) {
			$this->data['product_id'] = $value;
		}

		public function set_woo_cart_id( $value ) {
			$this->data['woo_cart_id'] = $value;
		}
	}

}