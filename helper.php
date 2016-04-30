<?php

// Get all the gravity forms
if ( ! function_exists( 'et_builder_gravity_forms' ) ) :
function et_builder_gravity_forms(){
	$output = '';
	global $wpdb; // database object in wordpress
	$forms = $wpdb->get_results("SELECT * FROM wp_rg_form WHERE is_active = 1"); // retreiving query
	if($forms){
		$output .= '<select id="et_pb_select_form" name="et_pb_select_form"><option value="">Select a Form</option>';
		foreach ( $forms as $form ) {
		$selected = sprintf(
			'<%%= typeof( et_pb_select_form ) !== "undefined" && "%1$s" === et_pb_select_form ?  " selected=\'selected\'" : "" %%>',
			esc_html($form->id )
		);
		$output .=	sprintf('<option value="%1$s"%2$s>%3$s</option>', esc_attr( $form->id ), $selected, esc_html( $form->title )); // add all the forms in the select option
		}
		$output .= '</select>';
	}
	return $output; // return all the forms
}
endif;
