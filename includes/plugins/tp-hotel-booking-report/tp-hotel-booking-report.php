<?php
/*
    Plugin Name: TP Hotel Booking Report Addon
    Plugin URI: http://thimpress.com/
    Description: TP Hotel Booking Report Addon
    Author: ThimPress
    Version: 1.0
*/
define( 'TP_HB_REPORT_DIR', plugin_dir_path( __FILE__ ) );
define( 'TP_HB_REPORT_URI', plugin_dir_url( __FILE__ ) );
define( 'TP_HB_REPORT_VER', 1.0 );

class TP_Hotel_Booking_Report
{

	public $is_hotel_active = false;

	function __construct()
	{
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		/**
		 * text-domain
		 */
		$this->load_textdomain();
	}

	/**
	 * load_textdomain
	 * @return null
	 */
	public function load_textdomain()
	{
		load_textdomain( 'tp-hotel-booking-report', TP_HB_REPORT_DIR . '/languages/' . get_locale() . '.mo' );
	}

	/**
	 * plugin loaded
	 * @return null
	 */
	function plugins_loaded()
	{
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if( class_exists( 'TP_Hotel_Booking' ) && is_plugin_active( 'tp-hotel-booking/tp-hotel-booking.php' ) )
		{
			$this->is_hotel_active = true;
		}

		if( ! $this->is_hotel_active ) {
			add_action( 'admin_notices', array( $this, 'add_notices' ) );
		}
		else
		{
			if( $this->is_hotel_active )
			{
				require_once TP_HB_REPORT_DIR . '/inc/functions.php';
				require_once TP_HB_REPORT_DIR . '/inc/class-hb-report.php';
				require_once TP_HB_REPORT_DIR . '/inc/class-hb-report-price.php';
				require_once TP_HB_REPORT_DIR . '/inc/class-hb-report-room.php';
			}
		}
	}

	/**
	 * notice messages
	 */
	function add_notices()
	{
		?>
			<div class="error">
				<p><?php _e( 'The <strong>TP Hotel Booking</strong> is not installed and/or activated. Please install and/or activate before you can using <strong>TP Hotel Booking Report</strong> add-on.'); ?></p>
			</div>
		<?php
	}

}

$hotel_block = new TP_Hotel_Booking_Report();