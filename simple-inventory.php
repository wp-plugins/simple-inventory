<?php
/**
 * Plugin Name: Simple Inventory
 * Plugin URI: http://www.wp-plugin-dev.com
 * Description: A simple Storage Plugin for WordPress.
 * Version: 0.11
 * Author: WP-Plugin-Dev.com
 * Author URI: http://www.wp-plugin-dev.com
 * Requires at least: 3.5
 * Tested up to: 3.9.1
 *
 **/

add_action( 'init', 'create_post_type' );

function create_post_type() {
	register_post_type( 'si_good',
		array(
			'labels' => array(
			'name' => __( 'Goods / Products' ),
			'singular_name' => __( 'Good / Product' )),
			'public' => true,
			'has_archive' => true,
			'rewrite' => array('slug' => 'inventory')
		)
	);
}


function simple_inventory_post_type() {
  $labels = array(
    'name' => __('Goods / Products','simple-inventory'),
    'singular_name' => __('Good / Product','simple-inventory'),
    'add_new' => __('Add New','simple-inventory'),
    'add_new_item' => __('Add New Good / Product','simple-inventory'),
    'edit_item' => __('Edit Good / Product','simple-inventory'),
    'new_item' => __('New Good / Product','simple-inventory'),
    'all_items' => __('All Goods / Products','simple-inventory'),
    'view_item' => __('View Good / Product','simple-inventory'),
    'search_items' => __('Search Goods / Products','simple-inventory'),
    'not_found' =>  __('No Goods / Products found','simple-inventory'),
    'not_found_in_trash' => __('No Goods / Products found in Trash','simple-inventory'), 
    'parent_item_colon' => '',
    'menu_name' => __('Goods / Products')
  );

  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true, 
    'show_in_menu' => true, 
    'query_var' => true,
    'rewrite' => array( 'slug' => __('inventory','simple-inventory') ),
    'capability_type' => 'post',
    'has_archive' => true, 
    'hierarchical' => true,
    'menu_icon' => 'dashicons-cart',
    'menu_position' => null,
    'taxonomies' => array('category'),
    'supports' => array( 'title', 'revisions','excerpt','thumbnail' )
  ); 

  register_post_type( 'si_good', $args );
remove_post_type_support( 'si_good', 'editor' ); 
}
add_action( 'init', 'simple_inventory_post_type' );


/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function simple_inventory_add_meta_box() {

	$screens = array( 'si_good' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'simple_inventory_sectionid',
			__( 'physical inventory data', 'simple-inventory' ),
			'simple_inventory_meta_box_callback',
			$screen
		);
	}
}
add_action( 'add_meta_boxes', 'simple_inventory_add_meta_box' );
//add_theme_support( 'post-thumbnails' ); 
/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function simple_inventory_meta_box_callback( $post ) {


	wp_nonce_field( 'simple_inventory_meta_box', 'simple_inventory_meta_box_nonce' );

	$sku = get_post_meta( $post->ID, '_SKU', true );
	$quantity = get_post_meta( $post->ID, '_Quantity', true );
	$ean = get_post_meta( $post->ID, '_EAN', true );
	
	echo '<table>';
	echo '<tr><td><label for="simple_inventory_SKU">';
	_e( 'Stock Keeping Unit', 'simple-inventory' );
	echo '</label></td><td>';
	echo '<input type="text" id="simple_inventory_SKU" name="simple_inventory_SKU" value="' . esc_attr( $sku ) . '" size="25" />';
	echo '</td></tr>';
	
	echo '<tr><td><label for="simple_inventory_EAN">';
	_e( 'EAN Number', 'simple-inventory' );
	echo '</label></td><td>';
	echo '<input type="text" id="simple_inventory_EAN" name="simple_inventory_EAN" value="' . esc_attr( $ean ) . '" size="25" />';
	echo '</td></tr>';
	
	echo '<tr><td><label for="simple_inventory_Quantity">';
	_e( 'Quantity', 'simple-inventory' );
	echo '</label></td><td>';
	echo '<input type="text" id="simple_inventory_Quantity" name="simple_inventory_Quantity" value="' . esc_attr( $quantity ) . '" size="25" /></td></tr>';
	echo "</table>";


}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function simple_inventory_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set.
	if ( ! isset( $_POST['simple_inventory_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['simple_inventory_meta_box_nonce'], 'simple_inventory_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */
	
	// Make sure that it is set.
	if ( ! isset( $_POST['simple_inventory_SKU'] ) ) {
		return;
	}

	// Sanitize user input.
	$si_data = sanitize_text_field( $_POST['simple_inventory_SKU'] );
	$si_data1 = sanitize_text_field( $_POST['simple_inventory_EAN'] );
	$si_data2 = sanitize_text_field( $_POST['simple_inventory_Quantity'] );
	
	
	// Update the meta field in the database.
	update_post_meta( $post_id, '_SKU', $si_data );
	update_post_meta( $post_id, '_EAN', $si_data1 );
	update_post_meta( $post_id, '_Quantity', $si_data2 );
	
}
add_action( 'save_post', 'simple_inventory_save_meta_box_data' );



//COLUMNS

add_filter( 'manage_edit-si_good_columns', 'edit_simple_inventory_columns' ) ;

function edit_simple_inventory_columns( $columns ) {

	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => __( 'Good / Product',"simple-inventory" ),
		
		'quantity' => __( 'Quantity' ),
		'sku' => __( 'SKU' ),

		'date' => __( 'Date' )
	);

	return $columns;
}

add_action( 'manage_si_good_posts_custom_column', 'my_manage_simple_inventory_columns', 10, 2 );

function my_manage_simple_inventory_columns( $column, $post_id ) {
	global $post;

	switch( $column ) {


		case 'quantity' :

			$quantity = get_post_meta( $post_id, '_Quantity', true );
			echo $quantity;	
			break;
			
		case 'sku' :

			/* Get the post meta. */
			$sku = get_post_meta( $post_id, '_SKU', true );
			echo $sku;
			break;	

		/* Just break out of the switch statement for everything else. */
		default :
			break;
	}
}

add_filter( 'manage_edit-si_good_sortable_columns', 'my_simple_inventory_sortable_columns' );

function my_simple_inventory_sortable_columns( $columns ) {

	$columns['quantity'] = 'quantity';
	$columns['sku'] = 'sku';

	return $columns;
}

/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'my_edit_si_good_load' );

function my_edit_si_good_load() {
	add_filter( 'request', 'my_sort_simple_inventory' );
}

/* Sorts the movies. */
function my_sort_simple_inventory( $vars ) {

	/* Check if we're viewing the 'movie' post type. */
	if ( isset( $vars['post_type'] ) && 'si_good' == $vars['post_type'] ) {

		/* Check if 'orderby' is set to 'duration'. */
		if ( isset( $vars['orderby'] ) && 'quantity' == $vars['orderby'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_Quantity',
					'orderby' => 'meta_value_num'
				)
			);
		}else if ( isset( $vars['orderby'] ) && 'sku' == $vars['orderby'] ) {

			/* Merge the query vars with our custom variables. */
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => '_SKU',
					'orderby' => 'meta_value'
				)
			);
		}
	}

	return $vars;
}

?>