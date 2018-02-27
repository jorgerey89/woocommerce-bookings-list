<?php
/*
 * Plugin Name: WooCommerce Bookings List
 * Description: Plugin to draw a list of reservations by selecting a start date of "Woocommerce Bookings" in the Wordpress Admin Panel
 * Plugin URI: https://www.woland.es/
 * Author: Jorge Rey
 * Author URI: https://www.woland.es/
 * Tags: woocommerce, woocommerce bookings
 * Text Domain: woocommerce-bookings-list-table
 * Domain Path: /languages
 * Version: 1.0.0
 */

if(is_admin())
{
    new woocommerce_bookings_list_table();
}

function my_plugin_load_plugin_textdomain() {
    load_plugin_textdomain( 'woocommerce-bookings-list-table', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'my_plugin_load_plugin_textdomain' );

class woocommerce_bookings_list_table
{
   
    public function __construct()
    {
        add_action( 'admin_menu', array($this, 'add_menu_list_table_page' ));
    }

    public function add_menu_list_table_page()
    {
        add_menu_page( __( 'Bookings List', 'woocommerce-bookings-list-table' ),__( 'Bookings List', 'woocommerce-bookings-list-table' ), 'manage_options', 'woocommerce-bookings-list-table', array($this, 'list_table_page'),'dashicons-calendar-alt',56 );
    }
  
    public function list_table_page()
    {
		
	global $wp_scripts;
	global $post, $booking;
	
	$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.11.4';
	$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	
	wp_enqueue_style( 'font-awesome',  plugin_dir_url( __FILE__ ) . 'assets/css/font-awesome.min.css', null, '4.7.0' );
	
	wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.min.css' );
	
	wp_enqueue_style( 'booking-lis-css',  plugin_dir_url( __FILE__ ) . 'assets/css/custom.css', null, '1.0.0' );
		
	wp_enqueue_script( 'date-picker-js', plugin_dir_url( __FILE__ ) . 'assets/js/date-picker' . $suffix . '.js', array( 'jquery', 'jquery-ui-datepicker' ), '1.0.0', true );
	
	wp_enqueue_script( 'jspdf-js', plugin_dir_url( __FILE__ ) . 'assets/js/jspdf.min.js', array( 'jquery' ), '1.3', true );
	wp_enqueue_script( 'jspdf-autotable-js', plugin_dir_url( __FILE__ ) . 'assets/js/jspdf.plugin.autotable.min.js', array( 'jquery' ), '2.3.2', true );
	
	wp_enqueue_script( 'csv-export-js', plugin_dir_url( __FILE__ ) . 'assets/js/csv-export.js', array( 'jquery', 'jquery-blockui' ), '1.0.0', true );
	wp_enqueue_script( 'booking-list-js', plugin_dir_url( __FILE__ ) . 'assets/js/booking-list.js', array( 'jquery', 'jquery-blockui' ), '1.0.0', true );
  
	?>
	<div class="wrap">
		<h2><? echo __( 'Bookings List', 'woocommerce-bookings-list-table' )?></h2>
			
		<h3><? echo __( 'Select Date', 'woocommerce-bookings-list-table' )?>:</h3>
		<form name="myform" method="POST" action="#" class="form-inline date-range">
			<div class="form-group">
				<label for="pickerfrom"><? echo __( 'Start Date', 'woocommerce-bookings-list-table' )?>:</label>
				
				<div class="input-group date">
				  <input id="pickerfrom"  name="from" class="form-control" placeholder="<? echo __( 'Start Date', 'woocommerce-bookings-list-table' )?>" type="text"><span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span>
				</div>
			</div>
				
		<button type="submit" title="Show" name="send" class="button action"><span><? echo __( 'Submit', 'woocommerce-bookings-list-table' )?></span></button>
		</form>
		
		
	 </div>
	<?php
	$from = $_POST['from'];
	
	if(isset($_POST['send'])){

		// Creating DateTime() objects from the input data.
		$dateTimeStart = new DateTime($from);
		$dateTimeEnd   = $dateTimeStart;

		// Get all Bookings in Range
		$bookings = WC_Bookings_Controller::get_bookings_in_date_range(
			$dateTimeStart->getTimestamp(),
			$dateTimeEnd->getTimestamp(),
			'',
			false
		  );
	
	
    ?>
	<h3><? echo __( 'Result of the date', 'woocommerce-bookings-list-table' )?>: <?php echo $from; ?></h3>
	
	<?
		if(!empty($bookings)){
	?>
		<a id="btnPrint" class="button action" href="#"><i class="fa fa-file-pdf-o" aria-hidden="true"></i> <? echo __( 'Export in PDF', 'woocommerce-bookings-list-table' )?></a>
		<a id="btncsv" class="button action"><i class="fa fa-file-excel-o" aria-hidden="true"></i> <? echo __( 'Export in CSV', 'woocommerce-bookings-list-table' )?></a>
    <table id="pdf" class="wp-list-table widefat fixed striped posts">
        <thead>
            <tr>
                <th><? echo __( 'ID', 'woocommerce-bookings-list-table' )?></th>
                <th><? echo __( 'Booked Product', 'woocommerce-bookings-list-table' )?></th>
                <th><? echo __( 'Booked By', 'woocommerce-bookings-list-table' )?></th>
				<th><? echo __( 'Persons', 'woocommerce-bookings-list-table' )?></th>
                <th><? echo __( 'Start Date', 'woocommerce-bookings-list-table' )?></th>
				<th><? echo __( 'Status', 'woocommerce-bookings-list-table' )?></th>  
            </tr>
        </thead>
        <tfoot>
            <tr>
				<th><? echo __( 'ID', 'woocommerce-bookings-list-table' )?></th>
                <th><? echo __( 'Booked Product', 'woocommerce-bookings-list-table' )?></th>
                <th><? echo __( 'Booked By', 'woocommerce-bookings-list-table' )?></th>
				<th><? echo __( 'Persons', 'woocommerce-bookings-list-table' )?></th>
                <th><? echo __( 'Start Date', 'woocommerce-bookings-list-table' )?></th>
				<th><? echo __( 'Status', 'woocommerce-bookings-list-table' )?></th>
            </tr>
        </tfoot>
        <tbody>
        <?php
		
		foreach ($bookings as $booking) {
		  $customer = $booking->get_customer();
		  $customer_name = esc_html( $customer->name ?: '-' );
		  $resource = $booking->get_resource();
		  $product = $booking->get_product();
		  $url_booking = admin_url( 'post.php?post=' . $booking->get_id() . '&action=edit' );
		  $url_product = admin_url( 'post.php?post=' . ( is_callable( array( $product, 'get_id' ) ) ? $product->get_id() : $product->id ) . '&action=edit' );
		  
		  echo '<tr>';	 
		  echo '<td> <a href="'.$url_booking.'">'.__( 'Booking', 'woocommerce-bookings-list-table' ).' #'.$booking->get_id().'</a></td>';
		  echo '<td> <a href="'.$url_product.'">'.$product->get_title().'</a></td>';
		  echo '<td> <a href="mailto:' . esc_attr( $customer->email ) . '">'.$customer_name.'</a></td>';
		  echo '<td>'.esc_html( array_sum( $booking->get_person_counts() ) ).'</td>';
		  echo '<td>'.$booking->get_start_date().'</td>';
		  echo '<td>'.esc_html( wc_bookings_get_status_label( $booking->get_status() ) ).'</td>';
		  echo "</tr>";
		}
		
        ?>
        </tbody>
    </table>

<?php
		}else{
			echo __( 'There is no reservation for the selected date.', 'woocommerce-bookings-list-table' );
		}
	}
}

}
?>