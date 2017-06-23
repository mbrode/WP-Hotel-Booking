<?php

/**
 * WP Hotel Booking extra class.
 *
 * @class       WPHB_Extra
 * @version     2.0
 * @package     WP_Hotel_Booking/Classes
 * @category    Class
 * @author      Thimpress, leehld
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;


if ( ! class_exists( 'WPHB_Extra' ) ) {

	/**
	 * Class WPHB_Extra.
	 *
	 * @since 2.0
	 */
	class WPHB_Extra {

		/**
		 * @var null
		 */
		static $_instances = null;

		/**
		 * WPHB_Extra constructor.
		 *
		 * @since 2.0
		 */
		public function __construct() {
			add_filter( 'hotel_booking_get_product_class', array( $this, 'product_class' ), 10, 3 );
			add_action( 'hotel_booking_room_details_quantity', array( $this, 'admin_booking_room_details' ), 10, 3 );
			add_action( 'admin_init', array( $this, 'save_extra' ) );
		}

		/**
		 * Extra product class.
		 *
		 * @param null $product
		 * @param null $product_id
		 * @param array $params
		 *
		 * @return WPHB_Extra_Package|null
		 */
		public function product_class( $product = null, $product_id = null, $params = array() ) {
			if ( ! $product_id || get_post_type( $product_id ) !== 'hb_extra_room' ) {
				return $product;
			}
			$parent_quantity = 1;
			if ( isset( $params['order_item_id'] ) ) {
				$parent_quantity = hb_get_order_item_meta( hb_get_parent_order_item( $params['order_item_id'] ), 'quantity', true );
			} else if ( ! is_admin() && isset( $params['parent_id'] ) && WP_Hotel_Booking::instance()->cart ) {
				$cart   = WPHB_Cart::instance();
				$parent = $cart->get_cart_item( $params['parent_id'] );
				if ( $parent ) {
					$parent_quantity = $parent->quantity;
				}
			}

			return new WPHB_Extra_Package( $product_id, array(
				'check_in_date'  => isset( $params['check_in_date'] ) ? $params['check_in_date'] : '',
				'check_out_date' => isset( $params['check_out_date'] ) ? $params['check_out_date'] : '',
				'room_quantity'  => $parent_quantity,
				'quantity'       => isset( $params['quantity'] ) ? $params['quantity'] : 1
			) );
		}


		/**
		 * Add extra in admin booking room details.
		 *
		 * @since 2.0
		 *
		 * @param $booking_params
		 * @param $search_key
		 * @param $room_id
		 */
		public function admin_booking_room_details( $booking_params, $search_key, $room_id ) {
			if ( ! isset( $booking_params[ $search_key ] ) ||
			     ! isset( $booking_params[ $search_key ][ $room_id ] ) ||
			     ! isset( $booking_params[ $search_key ][ $room_id ]['extra_packages_details'] )
			) {
				return;
			}
			$packages = $booking_params[ $search_key ][ $room_id ]['extra_packages_details'];
			?>
            <ul>
				<?php foreach ( $packages as $id => $package ): ?>
                    <li>
                        <small><?php printf( '%s (x%s)', $package['package_title'], $package['package_quantity'] ) ?></small>
                    </li>
				<?php endforeach ?>
            </ul>
			<?php
		}

		/**
		 * Save extra packages actions.
		 *
		 * @since 2.0
		 *
		 * @return bool|int|string|WP_Error
		 */
		public function save_extra() {
			if ( ! isset( $_POST ) || empty( $_POST ) ) {
				return false;
			}

			if ( ! isset( $_POST[ 'tp_hb_extra_room' ] ) || empty( $_POST[ 'tp_hb_extra_room' ] ) ) {
				return false;
			}

			foreach ( (array) $_POST[ 'tp_hb_extra_room' ] as $post_id => $post ) {

				global $wpdb;
				$query = $wpdb->prepare( "
				SELECT * FROM $wpdb->posts WHERE `ID` = %d AND `post_type` = %s
			", $post_id, 'hb_extra_room' );

				$results = $wpdb->get_results( $query, OBJECT );

				$args = array(
					'post_title'   => isset( $post['name'] ) ? $post['name'] : '',
					'post_content' => isset( $post['desc'] ) ? $post['desc'] : '',
					'post_type'    => 'hb_extra_room',
					'post_status'  => 'publish'
				);

				if ( ! $results ) {
					$post_id = wp_insert_post( $args );
				} else {
					$args['ID'] = $post_id;
					wp_update_post( $args );
				}

				if ( isset( $post['price'] ) ) {
					$price = (float) $post['price'];
				} else {
					$price = 0;
				}

				if ( get_post_meta( $post_id, 'tp_hb_extra_room_price', true ) || get_post_meta( $post_id, 'tp_hb_extra_room_price', true ) == 0 ) {
					update_post_meta( $post_id, 'tp_hb_extra_room_price', $price );
				} else {
					add_post_meta( $post_id, 'tp_hb_extra_room_price', $price );
				}

				unset( $post['name'] );
				unset( $post['desc'] );
				unset( $post['price'] );

				foreach ( $post as $key => $value ) {
					if ( get_post_meta( $post_id, 'tp_hb_extra_room_' . $key, true )
					     || get_post_meta( $post_id, 'tp_hb_extra_room_' . $key, true ) === ''
					     || get_post_meta( $post_id, 'tp_hb_extra_room_' . $key, true ) == 0
					) {
						update_post_meta( $post_id, 'tp_hb_extra_room_' . $key, $value );
					} else {
						add_post_meta( $post_id, 'tp_hb_extra_room_' . $key, $value );
					}
				}

				return $post_id;
			}

			return false;
		}

		/**
         * Get all extras to object.
         *
         * @since 2.0
         *
		 * @return array|null|object
		 */
		public function get_extra() {
			global $wpdb;
			$query = $wpdb->prepare( "
				SELECT * FROM $wpdb->posts WHERE `post_type` = %s
			", 'hb_extra_room' );

			return $wpdb->get_results( $query, OBJECT );
		}

		/**
		 * Get class instance.
		 *
		 * @return null|WPHB_Extra
		 */
		public static function instance() {
			if ( empty( self::$_instances ) ) {
				self::$_instances = new self();
			}

			return self::$_instances;
		}
	}

}
