<?php
class WF_Tracking_Settings {
	const TRACKING_PREMIUM_URL		= "https://www.xadapter.com/product/woocommerce-shipment-tracking-pro/";

	public function __construct() {
		$this->init();
	}

    public function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_'.WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY, array( $this, 'settings_tab') );
        add_action( 'woocommerce_update_options_'.WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY, array( $this, 'update_settings') );
    }

    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs[ WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY ] = __( 'Tracking', 'woocommerce-shipment-tracking' );
        return $settings_tabs;
    }

    public function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }

    public function update_settings() {
		$options = self::get_settings();
		foreach ( $options as $value ) {
			if ( ! isset( $value['id'] ) || ! isset( $value['type'] ) ) {
				continue;
			}

			if( WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_data_txt' == $value['id'] ) {
				// Do nothing.
			}

			if( WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_reset_data' == $value['id'] ) {
				// Reset tracking data is checked.
				if( isset( $_POST[ $value['id'] ] ) ) {
					unset ( $_POST[ $value['id'] ] );
					
					$tracking_data			= WfTrackingUtil::load_tracking_data( false, true );
					$tracking_data_txt 		= WfTrackingUtil::convert_tracking_data_to_piped_text( $tracking_data );
					
					$_POST[  WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_data_txt' ] = $tracking_data_txt;
					$result = delete_option( WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.WfTrackingUtil::TRACKING_DATA_KEY );
				}
				else {
					$tracking_data_txt 		= $_POST[  WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_data_txt' ];
					$default_tracking_data	= WfTrackingUtil::load_tracking_data();
					$tracking_data 			= WfTrackingUtil::convert_piped_text_to_tracking_data( $tracking_data_txt , $default_tracking_data);
					
					update_option( WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.WfTrackingUtil::TRACKING_DATA_KEY, $tracking_data );
				}
			}
		}

        woocommerce_update_options( $options );
    }

    public static function get_settings() {
		$tracking_data			= WfTrackingUtil::load_tracking_data();
		$tracking_data_txt		= WfTrackingUtil::convert_tracking_data_to_piped_text( $tracking_data );
		$message				= WfTrackingUtil::get_default_shipment_message_placeholder();
		
        $settings = array(
            'section_title'			=> array(
                'name'				=> __( 'Shipment Tracking Settings', 'woocommerce-shipment-tracking' ),
                'type'				=> 'title',
                'desc'				=> '',
                'id'				=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_section_title'
            ),
			'custom_message'		=> array(
				'title'				=> __( 'Custom Shipment Message.', 'woocommerce-shipment-tracking' ),
				'type'				=> 'text',
				'desc'				=> __( 'Define your own shipment message. Use the place holder tags [ID], [SERVICE] and [DATE] for Shipment Id, Shipment Service and Shipment Date respectively.<br/><p style="color:red">Defining your own custom shipment message is available in our <a href="'.self::TRACKING_PREMIUM_URL.'" target="_blank">premium version.</p><br>', 'woocommerce-shipment-tracking' ),
				'css'				=> 'width:900px',
				'id'				=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.WfTrackingUtil::TRACKING_MESSAGE_KEY,
				'placeholder'		=> $message
			),
			'turn_off_api'			=> array(
				'title'				=> __( 'Turn off API Status', 'woocommerce-shipment-tracking' ),
				'label'				=> __( 'Turn off Real time API Status', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Turn off real time API tracking status on customer order page. Basic Tracking info on top will still be  displayed. <br/><p style="color:red">API realtime tracking status for choosen services is available in our <a href="'.self::TRACKING_PREMIUM_URL.'" target="_blank">premium version.</p>', 'woocommerce-shipment-tracking' ),
				'id'				=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.WfTrackingUtil::TRACKING_TURN_OFF_API_KEY,
				'default'			=> 'no'
			),
			'turn_off_email_status'			=> array(
				'title'				=> __( 'Turn off Email Status', 'woocommerce-shipment-tracking' ),
				'label'				=> __( 'Turn off Email Tracking Status', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Turn off the feature Email Shipment Tracking Status to Customer for Order Completion email. <br/><p style="color:red">Email tracking status is available in our <a href="'.self::TRACKING_PREMIUM_URL.'" target="_blank">premium version.</p>', 'woocommerce-shipment-tracking' ),
				'id'				=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.WfTrackingUtil::TRACKING_TURN_OFF_EMAIL_STATUS_KEY,
				'default'			=> 'no'
			),
			'turn_off_csv_import'			=> array(
				'title'				=> __( 'Turn off CSV Import', 'woocommerce-shipment-tracking' ),
				'label'				=> __( 'Turn off CSV Tracking Import', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Turn off the feature CSV Tracking Import for bulk tracking data update. <br/><p style="color:red">CSV import feature (including FTP import) is available in our <a href="'.self::TRACKING_PREMIUM_URL.'" target="_blank">premium version.</p>', 'woocommerce-shipment-tracking' ),
				'id'				=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.WfTrackingUtil::TRACKING_TURN_OFF_CSV_IMPORT_KEY,
				'default'			=> 'no'
			),
            'data_txt' => array(
                'name'				=> __( 'Tracking Data', 'woocommerce-shipment-tracking' ),
                'type'				=> 'text',
				'css'				=> 'width:900px',
                'desc'				=> __( 'You can add or remove any shipment tracking services by adding or removing respective lines. <br/>To add new service, create a new line by adding shipper name and tracking url (optional) separated using pipe symbol \'|\' as given below. <br/>Format: <strong>[ shipping service name ] | [ shipment tracking url (optional) ]</strong><br/>Example: <strong>Shipping Service Name | http://tracking_url?tracking_id=</strong><br/><br/>Complex tracking urls can be represented using place holder tags [ID] and [PIN] for Shipment Id and Postcode respectively.<br/>Example: <strong>PostNL | https://jouw.postnl.nl/[ID]/track-en-trace/111111111/NL/[PIN]</strong><br/><p style="color:red">Support for multiple shipping services along with 70+ pre-filled services available in our <a href="'.self::TRACKING_PREMIUM_URL.'" target="_blank">premium version.</p><br>', 'woocommerce-shipment-tracking' ),
				'default'			=> $tracking_data_txt,
                'id'				=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_data_txt',
				'placeholder'		=> __( '[ shipping service name ]|[ shipment tracking url ]', 'woocommerce-shipment-tracking' )
            ),
			'reset_data'			=> array(
				'title'				=> __( 'Reset Tracking Data', 'woocommerce-shipment-tracking' ),
				'label'				=> __( 'Reset Tracking Data', 'woocommerce-shipment-tracking' ),
				'type'				=> 'checkbox',
				'desc'				=> __( 'Reset tracking data to the default values. All custom added values in the above tracking data will be cleaned up.', 'woocommerce-shipment-tracking' ),
				'id'				=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_reset_data',
				'default'			=> 'no'
			),
            'section_end' => array(
                 'type'			=> 'sectionend',
                 'id'			=> WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_section_end'
            ),
        );

        return apply_filters( WfTrackingUtil::TRACKING_SETTINGS_TAB_KEY.'_settings', $settings );
    }
}

new WF_Tracking_Settings();
