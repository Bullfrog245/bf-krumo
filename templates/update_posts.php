<?php
global $wpdb;
$today = date( 'Y-m-d' );

$programs = $wpdb->get_results("
	SELECT post_id
	FROM wp_postmeta
	WHERE meta_key = '_program_meta_end_date'
		AND meta_value >= '$today'
	ORDER BY meta_value ASC
");

// Update the custom fields of all active programs.
foreach ( $programs as $post )
{
	$new = array();
	$program = rs_get_program( $post->post_id );

	$fields = RS_Custom_Field::get_all();
	$default_fields = wp_list_filter( $fields, array( 'is_default' => 1, 'admin_only' => 0 ) );
	$program_fields = RS_Custom_Field::get_for_program( $program->ID );

	$expand_cf_box = count( $default_fields ) != count( $program_fields ); // good enough

	if ( 'auto-draft' == $program->post_status ) {
		$program_fields = $default_fields;
		//$expand_cf_box = false;
	}

	foreach ( (array) $fields as $field ) {
		if ( 'discount-code' == $field->field_slug && ! class_exists( 'RS_Enhanced_Plugin' ) )
			continue;

		if ( 'captcha' == $field->field_slug && ! class_exists( 'ReallySimpleCaptcha' ) )
			continue;

		if ( $field->admin_only )
			continue;

		$program_field = array_shift( wp_list_filter( $program_fields, array( "ID" => $field->ID ) ) );

		//---------------------------------

		if ( $field->ID == 22 && ! empty( $program_field->ID ) )
		{
			$new[74]['use'] = '1';
			$new[74]['require'] = '';
		}

		if ( $field->ID == 74 )
		{
			continue;
		}

		//---------------------------------

		$new[$field->ID]['use'] = empty( $program_field->ID ) ? '' : '1';
		$new[$field->ID]['require'] = empty( $program_field->is_required ) ? '' : '1';



	} // end foreach

	// RS_Program::update_registrations_fields( $post->post_id, $new );
	// echo $program->post_title . ' UPDATED!<br />';
}
?>

<p>No updates performed.</p>