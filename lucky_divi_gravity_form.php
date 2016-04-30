<?php
/*
Plugin Name: Divi & Gravity Forms
Description: Divi module for Gravity Forms.
Version: 1.3.3
Author: Lucky
License: GPLv2
*/
// Prevent direct access to the plugin
if ( ! defined( 'WPINC' ) ) {die;}

include_once plugin_dir_path( __FILE__ ) . '/helper.php'; //Include helper functions to be used by modules

add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );

add_action('et_builder_ready', 'GRAVITY_module');

function GRAVITY_module( )
{
	/* Add Gravity Form Module In The Page Builder Of Divi theme*/
	$directories = glob(get_stylesheet_directory() . '/modules/*' , GLOB_ONLYDIR);
	foreach ($directories as $filename) {
		include_once ($filename.'/init.php');
	}

	class ET_Builder_Module_Gravity extends ET_Builder_Module {

		/*Initialize Gravity Module*/
		function init() {
			$this->name = esc_html__( 'Gravity Forms', 'et_builder' ); // Name of the module
			$this->slug = 'et_pb_gravity';  // slug of module
			// General tab options
			$this->whitelisted_fields = array(
				'select_form',
				'admin_label',
				'module_id',
				'module_class',
				'max_width',
				'max_width_tablet',
				'max_width_phone',
				'other_options',
			);
			$this->main_css_element = '%%order_class%%';
			// Advanced tab options
			$this->advanced_options = array(
				'custom_margin_padding' => array(
					'css' => array(
						'important' => 'all',
					),
				),
			);
		}
		// To display the fields on front end
		function get_fields() {
			$fields = array(
				'select_form' => array(
					'label'             => esc_html__( 'Select a Form', 'et_builder' ),
					'type'              => 'select',
					'option_category'   => 'configuration',
					'renderer'			=> 'et_builder_gravity_forms',
					'description'       => esc_html__( 'Select a form below to add it to your post or page.', 'et_builder' ),

				),
				'other_options' => array(
					'label'           => esc_html__( 'Other Options', 'et_builder' ),
					'type'            => 'multiple_checkboxes',
					'option_category' => 'configuration',
					'options'         => array(
						'display'   => esc_html__( 'Display ', 'et_builder' ),
						'displaydescription'  => esc_html__( 'Display from description', 'et_builder' ),
						'enableajax' => esc_html__( 'Enable Ajax', 'et_builder' ),
					),
				),
				'max_width' => array(
					'label'           => esc_html__( 'Max Width', 'et_builder' ),
					'type'            => 'text',
					'option_category' => 'layout',
					'mobile_options'  => true,
					'tab_slug'        => 'advanced',
					'validate_unit'   => true,
				),
				'max_width_tablet' => array(
					'type' => 'skip',
				),
				'max_width_phone' => array(
					'type' => 'skip',
				),
				'disabled_on' => array(
					'label'           => esc_html__( 'Disable on', 'et_builder' ),
					'type'            => 'multiple_checkboxes',
					'options'         => array(
						'phone'   => esc_html__( 'Phone', 'et_builder' ),
						'tablet'  => esc_html__( 'Tablet', 'et_builder' ),
						'desktop' => esc_html__( 'Desktop', 'et_builder' ),
					),
					'additional_att'  => 'disable_on',
					'option_category' => 'configuration',
					'description'     => esc_html__( 'This will disable the module on selected devices', 'et_builder' ),
				),
				'admin_label' => array(
					'label'       => esc_html__( 'Admin Label', 'et_builder' ),
					'type'        => 'text',
					'description' => esc_html__( 'This will change the label of the module in the builder for easy identification.', 'et_builder' ),
				),
				'module_id' => array(
					'label'           => esc_html__( 'CSS ID', 'et_builder' ),
					'type'            => 'text',
					'option_category' => 'configuration',
					'tab_slug'        => 'custom_css',
					'option_class'    => 'et_pb_custom_css_regular',
				),
				'module_class' => array(
					'label'           => esc_html__( 'CSS Class', 'et_builder' ),
					'type'            => 'text',
					'option_category' => 'configuration',
					'tab_slug'        => 'custom_css',
					'option_class'    => 'et_pb_custom_css_regular',
				),
			);
			return $fields;
		}
		// to store the value of fields in database and display the form at front end
		function shortcode_callback( $atts, $content = null, $function_name ) {
			// storing the values in databse
			$module_id            = $this->shortcode_atts['module_id'];
			$module_class         = $this->shortcode_atts['module_class'];
			$other_options        = $this->shortcode_atts['other_options'];
			$select_form          = $this->shortcode_atts['select_form'];
			$max_width            = $this->shortcode_atts['max_width'];
			$max_width_tablet     = $this->shortcode_atts['max_width_tablet'];
			$max_width_phone      = $this->shortcode_atts['max_width_phone'];
			// storing process finish

			$module_class = ET_Builder_Element::add_module_order_class( $module_class, $function_name );


			$this->shortcode_content = et_builder_replace_code_content_entities( $this->shortcode_content );

			// Getting values of the options for gravity form
			if ( isset( $this->shortcode_atts['other_options'] ) && '' !== $this->shortcode_atts['other_options'] ) {
				$other_options_array = explode( '|', $this->shortcode_atts['other_options'] );
				if($other_options_array[0] == 'on'){
					$title = 'true';
				}else{
					$title = 'false';
				}
				if($other_options_array[1] == 'on'){
					$description = 'true';
				}else{
					$description = 'false';
				}
				if($other_options_array[2] == 'on'){
					$ajax = 'true';
				}else{
					$ajax = 'false';
				}
			}
			// creating the shortcode for gravity form
			$result = do_shortcode('[gravityform id="'.$select_form.'" title="'.$title.'" description="'.$description.'" ajax="'.$ajax.'"]');
			// Setting the width for different platforms
			if ( '' !== $max_width_tablet || '' !== $max_width_phone || '' !== $max_width ) {
				$max_width_values = array(
					'desktop' => $max_width,
					'tablet'  => $max_width_tablet,
					'phone'   => $max_width_phone,
				);
				et_pb_generate_responsive_css( $max_width_values, '%%order_class%%', 'max-width', $function_name );
			}
			// define the class values of the div displaying the gravity form
			$class = "et_pb_select_form_{select_form}";
			// setting the output to display the gravity form
			$output = sprintf(
				'<div%3$s class="et_pb_gravity%2$s%4$s">
					%1$s
				</div> <!-- .et_pb_gravity -->',
				$result,
				esc_attr( $class ),
				( '' !== $module_id ? sprintf( ' id="%1$s"', esc_attr( $module_id ) ) : '' ),
				( '' !== $module_class ? sprintf( ' %1$s', esc_attr( $module_class ) ) : '' )
			);

			return $output;
		}
	}
new ET_Builder_Module_Gravity;
}