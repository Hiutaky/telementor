<?php

use Elementor\Controls_Manager;
/**
 * Class Telegram_Action
 * @see https://alessandrodecristofaro.it/telementor?ref=class_php
 * Custom elementor form action after submit to add a subsciber to
 * Sendy list via API
 */
class Telegram_Action extends \ElementorPro\Modules\Forms\Classes\Action_Base {
	/**
	 * Get Name
	 *
	 * Return the action name
	 *
	 * @access public
	 * @return string
	 */
	public function get_name() {
		return 'telementor-action';
	}

	/**
	 * Get Label
	 *
	 * Returns the action label
	 *
	 * @access public
	 * @return string
	 */
	public function get_label() {
		return __( 'Telegram SendMessage', 'telementor' );
	}

	/**
	 * Run
	 *
	 * Runs the action after submit
	 *
	 * @access public
	 * @param \ElementorPro\Modules\Forms\Classes\Form_Record $record
	 * @param \ElementorPro\Modules\Forms\Classes\Ajax_Handler $ajax_handler
	 */
	public function run( $record, $ajax_handler ) {
		$settings = $record->get( 'form_settings' );
    	$message = ' ' . $settings['tg_message'];
    	$raw_fields = $record->get( 'fields' );

    	// Normalize the Form Data
		$fields = [];
		
    	foreach ( $raw_fields as $id => $field ) {
      		if( strpos($message, '[field id="' . $id . '"]') ){
        		$message = str_replace('[field id="' . $id . '"]', $field['value'], $message);
     		}
     		$fields[ $id ] = $field['value'];
		}
		$message = str_replace('/', '', $message);

        if( $settings['tg_sendTo'] == '' || $settings['tg_access_token'] == ''  || $message == '' ){
            $ajax_handler->add_error_message('Telementor Form Settings is not setup correrctly. Please insert all the Information or Contact the Webmaster.');
        }

        $api_data = [
            'chat_id' => $settings['tg_sendTo'],
            'text' => $message,
            'parse_mode' => 'HTML',
        ];


    	$url = 'https://api.telegram.org/bot' . $settings['tg_access_token'] . '/sendMessage';

		// Send the request
		$ret = wp_remote_post( $url, [
			'body' => $api_data,
        	]
     	);

		$ret_code = wp_remote_retrieve_response_code($ret);

   		if($ret_code != 200 || $ret_code == 400) {
			$body = json_decode(wp_remote_retrieve_body($ret), true );
			$ajax_handler->add_error_message(
				'Unable to Send Message to Telegram. 
				| Error: ' . $body['description'] . 
				'| Code: ' . $body['error_code'] 
			);
		}
	}

	/**
	 * Register Settings Section
	 *
	 * Registers the Action controls
	 *
	 * @access public
	 * @param \Elementor\Widget_Base $widget
	 */
	public function register_settings_section( $widget ) {
		$widget->start_controls_section(
			'telementor_section',
			[
				'label' => __( 'Telegram SendMessage', 'telementor' ),
				'condition' => [
					'submit_actions' => $this->get_name(),
				],
			]
		);


		$widget->add_control(
			'tg_access_token',
			[
				'label' => __( 'Access Token', 'telementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
			]
		);

		$widget->add_control(
			'tg_sendTo',
			[
				'label' => __( 'Send To', 'telementor' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
        	'placeholder' => __('es. @youruser or +39333...', 'telementor'),
			]
    	);

    	$widget->add_control(
    	  'tg_message',
    	    [
        	'label' => __('Message', 'telementor'),
        	'type' => \Elementor\Controls_Manager::TEXTAREA,
        	'description' => __('Use <b>[field id="field_id"]</b> to grab data from the Form Fields. (HTML Format is allowed). ', 'telementor'),
        	'dynamic' => [
          		'active' => true,
				]
      		]
		);
		
		$widget->add_control(
			'help',
			[
				'label' => '<a href="https://alessandrodecristofaro.it/telementor-help?ref=elementor-editor">Need help ?</a>',
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'separator' => 'before',
			]
		);

		$widget->end_controls_section();


	}

	/**
	 * On Export
	 *
	 * Clears form settings on export
	 * @access Public
	 * @param array $element
	 */
	public function on_export( $element ) {
		unset(
			$element['tg_access_token'],
			$element['tg_message'],
     	    $element['tg_sendTo']
		);
	}
}
