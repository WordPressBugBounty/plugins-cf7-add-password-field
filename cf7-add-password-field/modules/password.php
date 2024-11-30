<?php
/**
** A base module for the following types of tags:
**      [password] and [password*]              # Single-line password
**/

// Activate Language Files for WordPress 3.7 or lator
load_plugin_textdomain('cf7-add-password-field');

function wpcf7_add_form_tag_k_password() {
	$features = array( 'name-attr' => true);
	$features = apply_filters( 'cf7-add-password-field-features',$features );
	wpcf7_add_form_tag( array('password','password*'),
		'wpcf7_k_password_form_tag_handler',$features );
}

function wpcf7_k_password_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}

	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type, 'wpcf7-text' );
	
	$class .= ' wpcf7-validates-as-password';
		
	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['size'] = $tag->get_size_option( '40' );
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['minlength'] = $tag->get_minlength_option();

	if ( $atts['maxlength'] && $atts['minlength']
	&& $atts['maxlength'] < $atts['minlength'] ) {
		unset( $atts['maxlength'], $atts['minlength'] );
	}

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );

	$atts['autocomplete'] = $tag->get_option( 'autocomplete',
		'[-0-9a-zA-Z]+', true );

	$atts['password_strength'] = (int)$tag->get_option( 'password_strength', 'signed_int', true);
	$atts['password_check'] = $tag->get_option( 'password_check', '', true);
	$atts['specific_password_check'] = $tag->get_option( 'specific_password_check', '', true);
	$atts['hideIcon'] = $tag->has_option( 'hideIcon' );	
	
	$atts['Icon_position'] = $tag->get_option( 'Icon_position', '', true);
	$atts['Icon_float'] = $tag->get_option( 'Icon_float', '', true);
	$atts['Icon_top'] = $tag->get_option( 'Icon_top', '', true);
	$atts['Icon_margin'] = $tag->get_option( 'Icon_margin', '', true);
	$atts['Icon_marginleft'] = $tag->get_option( 'Icon_marginleft', '', true);
	
	if($tag->has_option( 'Icon_position' ) && !empty($atts['Icon_position'])){
		$style_attrib = 'position:'. $atts['Icon_position'] . '; ';
		if($tag->has_option( 'Icon_float' ) && !empty($atts['Icon_float']))
			$style_attrib .= 'float:'. $atts['Icon_float'] . '; ';
		if($tag->has_option( 'Icon_top' ) && !empty($atts['Icon_top']))
			$style_attrib .= 'top:'. $atts['Icon_top'] . '; ';
		if($tag->has_option( 'Icon_margin' ) && !empty($atts['Icon_margin']))
			$style_attrib .= 'margin:'. $atts['Icon_margin'] . '; ';
		if($tag->has_option( 'Icon_marginleft' ) && !empty($atts['Icon_marginleft']))
			$style_attrib .= 'margin-left:'. $atts['Icon_marginleft'] . '; ';			
	}else{
		$style_attrib = 'position: relative; margin-left: -30px;';
	}

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	if ( $validation_error ) {
		$atts['aria-invalid'] = 'true';
		$atts['aria-describedby'] = wpcf7_get_validation_error_reference(
			$tag->name
		);
	} else {
		$atts['aria-invalid'] = 'false';
	}
	
	$value = (string) reset( $tag->values );
	
	// Support placeholder. Reference: modules/date.php in the contact form 7 plugin.
	if ( $tag->has_option( 'placeholder' )
	or $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	}
	
	$value = $tag->get_default_option( $value );

	$value = wpcf7_get_hangover( $tag->name, $value );

	$atts['value'] = $value;

	if ( wpcf7_support_html5() ) {
		$atts['type'] = $tag->basetype;
	} else {
		$atts['type'] = 'password';
	}
	$atts['name'] = $tag->name;

	$atts = wpcf7_format_atts( $atts );

	$tag_id = $tag->get_id_option();
	if( empty($tag_id) ) $tag_id = $tag->name; // for the version 5.8 of Contact form 7: Contact form 7 ignores the id attribute if the same ID is already used for another element.

	if( $tag_id === $tag->name && !$tag->has_option( 'hideIcon' ) ){
 		  $html = sprintf(
			'<span class="wpcf7-form-control-wrap" data-name="%1$s"><input %2$s />%3$s<span style="'. $style_attrib .'"  id="buttonEye-'. $tag_id .'" class="fa fa-eye-slash" onclick="pushHideButton(\''. $tag_id .'\')"></span></span>',
			sanitize_html_class( $tag->name ), $atts, $validation_error );
	}else{
		$html = sprintf(
			'<span class="wpcf7-form-control-wrap" data-name="%1$s"><input %2$s />%3$s</span>',
			sanitize_html_class( $tag->name ), $atts, $validation_error );
	}
	return $html;
}

function wpcf7_k_password_validation_filter( $result, $tag ) {
	$name = $tag->name;

	$value = isset( $_POST[$name] )
		? trim( wp_unslash( strtr( (string) $_POST[$name], "\n", " " ) ) )
		: '';

	$specific_password_check = $tag->get_option( 'specific_password_check', '', true);
	if(!empty($specific_password_check)){
		$value_pass_array = explode("_", str_replace(" ", "", $specific_password_check));
		$flag = false;
		foreach($value_pass_array as $each_value_pass){
			if($value === $each_value_pass ){
				$flag = true;
				 break;
			}
		}
		if( $flag === false){
			$result->invalidate($tag, __("Passwords do not match defined!", 'cf7-add-password-field' ));		
		}
	}

	$password_check = $tag->get_option( 'password_check', '', true);
	if(!empty($password_check)){
		if(isset( $_POST[$password_check] )){
			$value_pass = isset( $_POST[$password_check] )
		? trim( wp_unslash( strtr( (string) $_POST[$password_check], "\n", " " ) ) )
		: '';
			if($value !== $value_pass ){
					$result->invalidate($tag, __("Passwords do not match!", 'cf7-add-password-field' ));		
			}
		}
	}

	$password_strength = (int)$tag->get_option( 'password_strength','signed_int', true);

	if ($password_strength < 0){
		$password_strength = 0;
	}

	$pattern = preg_quote ($tag->get_option( 'pattern' ));

	if ( $tag->is_required() and '' === $value ) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
	}elseif ( '' !== $value ){
		$maxlength = $tag->get_maxlength_option();
		$minlength = $tag->get_minlength_option();
		if ( $maxlength and $minlength and $maxlength < $minlength ) {
			$maxlength = $minlength = null;
		}
		$code_units = wpcf7_count_code_units( $value );
		if ( false !== $code_units ) {
			if ( $maxlength and $maxlength < $code_units ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_long' ) );
			} elseif ( $minlength and $code_units < $minlength ) {
				$result->invalidate( $tag, wpcf7_get_message( 'invalid_too_short' ) );
			}
		}

		if ($password_strength > 0) {
			if($password_strength === 1){
				if(!preg_match("/^[0-9]+$/", $value)){
					$result->invalidate($tag, __("Please use the numbers only", 'cf7-add-password-field' ));
				}
			}elseif($password_strength === 2){
				if(!preg_match("/([0-9].*[a-z,A-Z])|([a-z,A-Z].*[0-9])/", $value) ){
					$result->invalidate($tag, __("Please include one or more letters and numbers.", 'cf7-add-password-field' ));
				}
			}elseif($password_strength === 3){
				if(!preg_match("/[0-9]/", $value) or
				 !preg_match("/([a-z].*[A-Z])|([A-Z].*[a-z])/", $value)){
					$result->invalidate($tag, __("Please include one or more upper and lower case letters and numbers.", 'cf7-add-password-field' ));
				}
			}elseif($password_strength === 4){
				if(!preg_match("/[0-9]/", $value) or
				 !preg_match("/([a-z].*[A-Z])|([A-Z].*[a-z])/", $value) or 
				 !preg_match("/([!,%,&,@,#,$,^,*,?,_,~])/", $value)){
					$result->invalidate($tag, __("Please include one or more upper and lower case letters, numbers, and marks.", 'cf7-add-password-field' ));
				}
			}
		}
	}

	return apply_filters('wpcf7_k_password_validation_filter', $result, $tag);
}

// Add Tag.
if ( is_admin() ) {
	add_action( 'wpcf7_admin_init' , 'wpcf7_k_password_add_tag_generator' , 55 );
}

function wpcf7_k_password_add_tag_generator(){
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 'password', __( 'Password', 'cf7-add-password-field' ),
		'wpcf7_k_password_pane_confirm', array( 'nameless' => 1, 'version'=>'2') );
}

function wpcf7_k_password_pane_confirm( $contact_form, $options) {
	$field_types = array(
		'password' => array(
			'display_name' => __( 'Password field', 'cf7-add-password-field' ),
			'heading' => __( 'Password field form-tag generator', 'cf7-add-password-field' ),
			'description' => __( 'Generate a form-tag for a password button.', 'cf7-add-password-field' ),
			'maybe_purpose' => 'author_name',
		)
	);

	$basetype = $options['id'];

	if ( ! in_array( $basetype, array_keys( $field_types ) ) ) {
		$basetype = 'password';
	}

	$tgg = new WPCF7_TagGeneratorGenerator( $options['content'] );
?>
<header class="description-box">
	<h3><?php
		echo esc_html( $field_types[$basetype]['heading'] );
	?></h3>

	<p><?php
		$description = wp_kses(
			$field_types[$basetype]['description'],
			array(
				'a' => array( 'href' => true ),
				'strong' => array(),
			),
			array( 'http', 'https' )
		);

		echo $description;
	?></p>
</header>

<div class="control-box">
	<?php
		$tgg->print( 'field_type', array(
			'with_required' => true,
			'select_options' => array(
				$basetype => $field_types[$basetype]['display_name'],
			),
		) );
		$tgg->print( 'field_name', array(
			'ask_if' => $field_types[$basetype]['maybe_purpose']
		) );
	?>
	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'name-option-legend' ) ); ?>"><?php
			echo esc_html( __( 'Name', 'contact-form-7' ) );
		?></legend>
		<label><?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'name-option-legend' ),
				'aria-describedby' => $tgg->ref( 'name-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'name:',
			) )
		);
		?></label>
	</fieldset>
	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'id-option-legend' ) ); ?>"><?php
			echo esc_html( __( 'Id attribute', 'contact-form-7' ) );
		?></legend>
		<label><?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'id-option-legend' ),
				'aria-describedby' => $tgg->ref( 'id-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'id:',
			) )
		);
		?></label>
	</fieldset>
	<?php

		$tgg->print( 'class_attr' );

		$tgg->print( 'default_value', array(
			'with_placeholder' => true,
		) );
	?>
	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'minlength-option-legend' ) ); ?>"><?php
			echo esc_html( __( 'Password Length', 'cf7-add-password-field' ) );
		?></legend>
		<label>Min <?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'minlength-option-legend' ),
				'aria-describedby' => $tgg->ref( 'minlength-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'minlength:',
			) )
		);
		?></label><br/>
		<?php echo esc_html( __( 'Required more than the specified number of characters the input.', 'cf7-add-password-field' ) ); ?><br/>
		<label>Max <?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'maxlength-option-legend' ),
				'aria-describedby' => $tgg->ref( 'maxlength-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'maxlength:',
			) )
		);
		?></label><br/>
		<?php echo esc_html( __( 'Required less than the specified number of characters the input.', 'cf7-add-password-field' ) ); ?>
	</fieldset>
	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'password_strength-option-legend' ) ); ?>"><?php
			echo esc_html( __( 'Password Strength', 'cf7-add-password-field' ) );
		?></legend>
		<label><?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'password_strength-option-legend' ),
				'aria-describedby' => $tgg->ref( 'password_strength-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'password_strength:',
			) )
		);
		?></label><br/>
		1 = <?php echo esc_html( __( 'Numbers only', 'cf7-add-password-field' ) ); ?><br/>
		2 = <?php echo esc_html( __( 'Include letters and numbers', 'cf7-add-password-field' ) ); ?><br/>
		3 = <?php echo esc_html( __( 'Include upper and lower case letters and numbers', 'cf7-add-password-field' ) ); ?><br/>
		4 = <?php echo esc_html( __( 'Include upper and lower case letters, numbers, and marks', 'cf7-add-password-field' ) ); ?>	
	</fieldset>
	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'password_check-option-legend' ) ); ?>"><?php
			echo esc_html( __( 'Password Check', 'cf7-add-password-field' ) );
		?></legend>
		<label><?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'password_check-option-legend' ),
				'aria-describedby' => $tgg->ref( 'password_check-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'password_check:',
			) )
		);
		?></label><br/>
		<?php echo esc_html( __( 'Enter the value of the “name” on the field if you wish to verify a value of a password field. In case of verifying the password value that you set [password password-100], set [password* password-101 password_check:password-100].', 'cf7-add-password-field' ) ); ?>
	</fieldset>
	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'specific_password_check-option-legend' ) ); ?>"><?php
			echo esc_html( __( 'Specific Password Check', 'cf7-add-password-field' ) );
		?></legend>
		<label><?php
		echo sprintf(
			'<input %s placeholder="password1_password2"/>',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'specific_password_check-option-legend' ),
				'aria-describedby' => $tgg->ref( 'specific_password_check-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'specific_password_check:',
			) )
		);
		?></label><br/>
		<?php echo esc_html( __( ' Enter your password separated by underline(Passwords cannot contain underline and marks escaped by preg_quote are not allowed.). Check if it matches the password entered here. If you have set a password strength, the password set here should also follow that rule.', 'cf7-add-password-field' ) ); ?>
	</fieldset>
	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'hideIcon-option-legend' ) ); ?>"><?php
			echo esc_html( __( 'Hide Icon', 'cf7-add-password-field' ) );
		?></legend>
		<label><?php
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'checkbox',
				'aria-labelledby' => $tgg->ref( 'hideIcon-option-legend' ),
				'aria-describedby' => $tgg->ref( 'hideIcon-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'hideIcon',
			) )
		);
		?></label><?php echo esc_html( __( 'Hide the icon that shows the password', 'cf7-add-password-field' ) ); ?><br/>
	</fieldset>
	<fieldset>
		<legend id="<?php echo esc_attr( $tgg->ref( 'icon_location-option-legend' ) ); ?>"><?php
			echo esc_html( __( 'Icon Location', 'cf7-add-password-field' ) );
		?></legend>
		<label><?php echo esc_html( __( 'If you wish to customize the position of the icons, please set the following stylesheet values.', 'contact-form-7' ) );?></label><br/>
		<label>posotion:
		<?php 
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'Icon_position-option-legend' ),
				'aria-describedby' => $tgg->ref( 'Icon_position-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'Icon_position:',
			) )
		);
		?></label><br/>
		<label>float:
		<?php 
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'Icon_float-option-legend' ),
				'aria-describedby' => $tgg->ref( 'Icon_float-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'Icon_float:',
			) )
		);
		?></label><br/>
		<label>top:
		<?php 
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'Icon_top-option-legend' ),
				'aria-describedby' => $tgg->ref( 'Icon_top-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'Icon_top:',
			) )
		);
		?></label><br/>	
		<label>margin:
		<?php 
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'Icon_margin-option-legend' ),
				'aria-describedby' => $tgg->ref( 'Icon_margin-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'Icon_margin:',
			) )
		);
		?></label><br/>	
		<label>margin-left:
		<?php 
		echo sprintf(
			'<input %s />',
			wpcf7_format_atts( array(
				'type' => 'text',
				'aria-labelledby' => $tgg->ref( 'Icon_marginleft-option-legend' ),
				'aria-describedby' => $tgg->ref( 'Icon_marginleft-option-description' ),
				'data-tag-part' => 'option',
				'data-tag-option' => 'Icon_marginleft:',
			) )
		);
		?></label>
	</fieldset>

</div>

<footer class="insert-box">
	<?php
		$tgg->print( 'insert_box_content' );

		$tgg->print( 'mail_tag_tip' );
	?>
</footer>

<?php
}