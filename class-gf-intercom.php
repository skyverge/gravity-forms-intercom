<?php
/**
 * Gravity Forms Intercom Add-on
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Gravity Forms Intercom Add-on to newer
 * versions in the future.
 *
 * @package   Gravity-Forms-Intercom
 * @author    SkyVerge
 * @category  Admin
 * @copyright Copyright (c) 2018, rocketgenius
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace RocketGenius\GravityForms\Intercom;

defined( 'ABSPATH' ) or exit;

use Intercom\IntercomClient;
use GuzzleHttp\Exception\GuzzleException as ClientException;

\GFForms::include_feed_addon_framework();

/**
 * Gravity Forms Intercom Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    SkyVerge
 * @copyright Copyright (c) 2018, Rocketgenius
 */
class GFIntercom extends \GFFeedAddOn {


	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Intercom Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from intercom.php
	 */
	protected $_version = GF_INTERCOM_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.14.26';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsintercom';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsintercom/intercom.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'http://www.gravityforms.com/';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Intercom Add-On';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Intercom';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines if only the first matching feed will be processed.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_single_feed_submission = true;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_intercom';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_intercom';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_intercom_uninstall';

	/**
	 * Defines the capabilities needed for the Intercom Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array(
		'gravityforms_intercom',
		'gravityforms_intercom_uninstall'
	);

	/**
	 * Contains an instance of the Intercom API library, if available.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    \Intercom\IntercomClient $api If available, contains an instance of the Intercom API library.
	 */
	protected $api = null;

	/**
	 * Contains an instance of the Intercom\Admin class, if available.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    object $admin If available, contains an instance of the Intercom Admin class.
	 */
	protected $admin = null;

	/**
	 * Caches admin users query when made to the Intercom API.
	 *
	 * @since 1.0
	 * @access protected
	 * @var    array $admin_users The admin users for the Intercom app.
	 */
	protected $admin_users = null;


	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return \RocketGenius\GravityForms\Intercom\GFIntercom
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new GFIntercom();
		}
		return self::$_instance;
	}


	/**
	 * Register needed plugin hooks and PayPal delayed payment support.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses \GFAddOn::is_gravityforms_supported()
	 * @uses \GFFeedAddOn::add_delayed_payment_support()
	 */
	public function init() {

		parent::init();

		require_once( 'includes/admin/class-gf-intercom-admin.php' );
		$this->admin = new Admin( $this->_slug );

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Create conversation in Intercom only when payment is received.', 'gravityformsintercom' ),
			)
		);
	}



	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------


	/**
	 * Setup plugin settings fields.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFIntercom::plugin_settings_description()
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		return [
			[
				'title'       => '',
				'description' => $this->plugin_settings_description(),
				'fields'      => [
					[
						'name'              => 'access_token',
						'label'             => __( 'Access Token', 'gravityformsintercom' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => [
							$this,
							'initialize_api'
						],
					],
					[
						'type'     => 'save',
						'messages' => [
							'success' => __( 'Intercom settings have been updated.', 'gravityformsintercom' ),
						],
					],
				],
			],
		];
	}


	/**
	 * Prepare plugin settings description.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return string
	 */
	public function plugin_settings_description() {

		// Prepare description.
		$description = sprintf(
			'<p>%s</p>',
			sprintf(
				esc_html__( 'Intercom makes it easy to provide your customers with a great support experience. Use Gravity Forms to collect customer information and automatically create a new Intercom conversation. If you don\'t have a Intercom account, you can %1$ssign up for one here.%2$s', 'gravityformsintercom' ),
				'<a href="https://www.intercom.com/" target="_blank">', '</a>'
			)
		);

		// Add API key location instructions.
		if ( ! $this->initialize_api() ) {

			$description .= '<p>' . sprintf(
					esc_html__( 'Gravity Forms Intercom Add-On requires an Access Token (with extended permissions). You can generate an Access Token %1$sby following the guidelines here%2$s.', 'gravityformsintercom' ),
					'<a href="https://developers.intercom.io/docs/personal-access-tokens">', '</a>'
				) . '</p>';

		}

		return $description;
	}



	// # FEED SETTINGS -------------------------------------------------------------------------------------------------


	/**
	 * Setup fields for feed settings.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFAddOn::add_field_after()
	 * @uses GFAddOn::get_first_field_by_type()
	 * @uses GFFeedAddOn::get_default_feed_name()
	 * @uses GFIntercom::file_fields_for_feed_setup()
	 * @uses GFIntercom::message_types_for_feed_setup()
	 * @uses GFIntercom::state_types_for_feed_setup()
	 * @uses GFIntercom::users_for_assign_setting()
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		$settings = [
			[
				'fields' => [
					[
						'name'          => 'feed_name',
						'type'          => 'text',
						'required'      => true,
						'class'         => 'medium',
						'label'         => esc_html__( 'Name', 'gravityformsintercom' ),
						'default_value' => $this->get_default_feed_name(),
						'tooltip'       => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Name', 'gravityformsintercom' ),
							esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityformsintercom' )
						),
					],
					[
						'name'     => 'notes_admin',
						'type'     => 'select',
						'onchange' => "jQuery(this).parents('form').submit();",
						'choices'  => $this->get_users_for_setting( 'notes' ),
						'label'    => esc_html__( 'Add notes from', 'gravityformsintercom' ),
						'tooltip'  => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Notes Admin', 'gravityformsintercom' ),
							esc_html__( 'If selected, new conversations can have a note from this admin (only plain text is used in messages by default, so this is helpful for all fields). <strong>This is required if you also want to assign conversations</strong>.', 'gravityformsintercom' )
						),
					],
					[
						'name'       => 'admin',
						'type'       => 'select',
						'dependency' => 'notes_admin',
						'choices'    => $this->get_users_for_setting( 'assign' ),
						'label'      => esc_html__( 'Assign to User', 'gravityformsintercom' ),
						'tooltip'    => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Assign to Admin', 'gravityformsintercom' ),
							esc_html__( 'Choose the Intercom Admin to assign this conversation when notes are added.', 'gravityformsintercom' )
						),
					],
				],
			],
			[
				'title'  => esc_html__( 'Customer Details', 'gravityformsintercom' ),
				'fields' => [
					[
						'name'          => 'customer_type',
						'type'          => 'select',
						'required'      => true,
						'choices'       => $this->get_intercom_user_types(),
						'label'         => esc_html__( 'Intercom user type', 'gravityformsintercom' ),
						'tooltip'    => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Intercom User Types', 'gravityformsintercom' ),
							esc_html__( 'Choose whether this feed creates a customer as a User or a Lead in Intercom.', 'gravityformsintercom' )
						),
					],
					[
						'name'          => 'customer_email',
						'type'          => 'field_select',
						'required'      => true,
						'label'         => esc_html__( 'Email Address', 'gravityformsintercom' ),
						'default_value' => $this->get_first_field_by_type( 'email' ),
						'args'          => [
							'input_types' => [
								'email',
								'hidden',
							]
						],
					],
					[
						'name'          => 'customer_first_name',
						'type'          => 'field_select',
						'label'         => esc_html__( 'First Name', 'gravityformsintercom' ),
						'default_value' => $this->get_first_field_by_type( 'name', 3 ),
					],
					[
						'name'          => 'customer_last_name',
						'type'          => 'field_select',
						'label'         => esc_html__( 'Last Name', 'gravityformsintercom' ),
						'default_value' => $this->get_first_field_by_type( 'name', 6 ),
					],
					[
						'name'          => 'customer_phone',
						'type'          => 'field_select',
						'required'      => false,
						'label'         => esc_html__( 'Phone Number', 'gravityformsintercom' ),
						'default_value' => $this->get_first_field_by_type( 'phone' ),
						'args'          => [
							'input_types' => [
								'phone',
								'hidden',
							]
						],
					],
				],
			],
			[
				'title'  => esc_html__( 'Message Details', 'gravityformsintercom' ),
				'fields' => [
					[
						'name'          => 'body',
						'type'          => 'textarea',
						'required'      => true,
						'use_editor'    => true,
						'class'         => 'large',
						'label'         => esc_html__( 'Message Body', 'gravityformsintercom' ),
						'default_value' => '{all_fields}',
					],
				],
			],
			[
				'title'  => esc_html__( 'Message Options', 'gravityformsintercom' ),
				'fields' => [
					[
						'name'    => 'status',
						'type'    => 'select',
						'choices' => $this->state_types_for_feed_setup(),
						'label'   => esc_html__( 'Message State', 'gravityformsintercom' ),
					],
					[
						'name'          => 'note',
						'type'          => 'textarea',
						'dependency'    => 'notes_admin',
						'use_editor'    => true,
						'class'         => 'medium',
						'label'         => esc_html__( 'Note', 'gravityformsintercom' ),
						'default_value' => '{all_fields}',
					],
				],
			],
			[
				'title'  => esc_html__( 'Feed Conditional Logic', 'gravityformsintercom' ),
				'fields' => [
					[
						'name'           => 'feed_condition',
						'type'           => 'feed_condition',
						'label'          => esc_html__( 'Conditional Logic', 'gravityformsintercom' ),
						'checkbox_label' => esc_html__( 'Enable', 'gravityformsintercom' ),
						'instructions'   => esc_html__( 'Export to Intercom if', 'gravityformsintercom' ),
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Conditional Logic', 'gravityformsintercom' ),
							esc_html__( 'When conditional logic is enabled, form submissions will only be exported to Intercom when the condition is met. When disabled, all form submissions will be posted.', 'gravityformsintercom' )
						),
					],
				],
			],
		];

		return $settings;
	}


	/**
	 * Prepare Intercom Users for feed settings field.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFAddOn::get_setting()
	 * @uses GFAddOn::log_error()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @param string $setting the setting type
	 * @return array
	 */
	public function get_users_for_setting( $setting = 'notes' ) {

		$label = ( 'assign' === $setting ) ? __( 'Do not assign', 'gravityformsintercom' ) : __( 'Do not add notes', 'gravityformsintercom' );

		// Initialize choices array.
		$choices = [
			[
				'label' => $label,
				'value' => '',
			],
		];

		// If Intercom instance is not initialized, return choices.
		if ( ! $this->initialize_api() ) {
			return $choices;
		}

		try {

			// Get users for app.
			$admins = ! is_null( $this->admin_users ) ? $this->admin_users : $this->api->admins->getAdmins();

			$this->admin_users = $admins;

		} catch ( ClientException $e ) {

			// Log that users could not be retrieved.
			$this->log_error( __METHOD__ . '(): Failed to get users for app; ' . $e->getMessage() );
			return $choices;
		}

		// If no users were found, return.
		if ( ! $admins ) {
			return $choices;
		}

		// Loop through users.
		foreach ( $admins->admins as $admin ) {

			// Add user as choice and skip team users.
			if ( 'admin' === $admin->type ) {
				$choices[] = [
					'label' => $admin->name,
					'value' => $admin->id,
				];
			}
		}

		return $choices;
	}


	/**
	 * Returns the Intercom user types for conversations.
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	public function get_intercom_user_types() {

		return [
			[
				'label' => esc_html__( 'User', 'gravityformsintercom' ),
				'value' => 'user',
			],
			[
				'label' => esc_html__( 'Lead', 'gravityformsintercom' ),
				'value' => 'lead',
			],
		];
	}


	/**
	 * Prepare Intercom Status Types for feed settings field.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function state_types_for_feed_setup() {

		return [
			[
				'label' => esc_html__( 'Open', 'gravityformsintercom' ),
				'value' => 'open',
			],
			[
				'label' => esc_html__( 'Snoozed', 'gravityformsintercom' ),
				'value' => 'snoozed',
			],
			[
				'label' => esc_html__( 'Closed', 'gravityformsintercom' ),
				'value' => 'closed',
			],
		];
	}



	// # FEED LIST -----------------------------------------------------------------------------------------------------


	/**
	 * Set feed creation control.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return bool
	 */
	public function can_create_feed() {
		return $this->initialize_api();
	}


	/**
	 * Enable feed duplication.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param int $feed_id Feed to be duplicated.
	 *
	 * @return bool
	 */
	public function can_duplicate_feed( $feed_id ) {
		return true;
	}


	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function feed_list_columns() {

		return [
			'feed_name'     => esc_html__( 'Name', 'gravityformsintercom' ),
			'customer_type' => esc_html__( 'Create as', 'gravityformsintercom' ),
			'notes_admin'   => esc_html__( 'Add notes from', 'gravityformsintercom' ),
			'admin'         => esc_html__( 'Assign to', 'gravityformsintercom' ),
		];
	}


	/**
	 * Returns the value to be displayed in the user name column.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $feed The current Feed object.
	 *
	 * @uses GFAddOn::log_error()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return string
	 */
	public function get_column_value_customer_type( $feed ) {

		// If no user ID is set, return not assigned.
		if ( rgblank( $feed['meta']['customer_type'] ) ) {
			return '';
		}

		return ucwords( rgars( $feed, 'meta/customer_type' ) );
	}


	/**
	 * Returns the value to be displayed in the user name column.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $feed The current Feed object.
	 *
	 * @uses GFAddOn::log_error()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return string
	 */
	public function get_column_value_user( $feed ) {

		// If no user ID is set, return not assigned.
		if ( rgblank( $feed['meta']['admin'] ) ) {
			return esc_html__( 'No User Assigned', 'gravityformsintercom' );
		}

		// If Intercom instance is not initialized, return user ID.
		if ( ! $this->initialize_api() ) {
			return rgars( $feed, 'meta/admin' );
		}

		try {

			// Get user for feed.
			$user = $this->api->admins->getAdmin( rgars( $feed, 'meta/admin' ) );

			return esc_html( $user->name );

		} catch ( ClientException $e ) {

			// Log that user could not be retrieved.
			$this->log_error( __METHOD__ . '(): Unable to get user for feed; ' . $e->getMessage() );
			return rgars( $feed, 'meta/admin' );

		}
	}


	/**
	 * Returns the value to be displayed in the user name column.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $feed The current Feed object.
	 *
	 * @uses GFAddOn::log_error()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return string
	 */
	public function get_column_value_notes_admin( $feed ) {

		// If no user ID is set, return not assigned.
		if ( rgblank( $feed['meta']['notes_admin'] ) ) {
			return esc_html__( 'No Notes Added', 'gravityformsintercom' );
		}

		// If Intercom instance is not initialized, return user ID.
		if ( ! $this->initialize_api() ) {
			return rgars( $feed, 'meta/notes_admin' );
		}

		try {

			// Get user for feed.
			$user = $this->api->admins->getAdmin( rgars( $feed, 'meta/notes_admin' ) );

			return esc_html( $user->name );

		} catch ( ClientException $e ) {

			// Log that user could not be retrieved.
			$this->log_error( __METHOD__ . '(): Unable to get notes user for feed; ' . $e->getMessage() );

			return rgars( $feed, 'meta/notes_admin' );
		}
	}


	// # FEED PROCESSING -----------------------------------------------------------------------------------------------


	/**
	 * Process feed, create conversation.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $feed The current Feed object.
	 * @param array $entry The current Entry object.
	 * @param array $form The current Form object.
	 *
	 * @uses GFAddOn::get_field_value()
	 * @uses GFAddOn::is_json()
	 * @uses GFAddOn::log_debug()
	 * @uses GFCommon::is_invalid_or_empty_email()
	 * @uses GFCommon::replace_variables()
	 * @uses GFFeedAddOn::add_feed_error()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return bool
	 */
	public function process_feed( $feed, $entry, $form ) {

		// If Intercom instance is not initialized, exit.
		if ( ! $this->initialize_api() ) {

			$this->add_feed_error( esc_html__( 'Unable to create conversation because API was not initialized.', 'gravityformsintercom' ), $feed, $entry, $form );
			return false;
		}

		// If this entry already has a Intercom conversation, exit.
		if ( gform_get_meta( $entry['id'], 'intercom_conversation_id' ) ) {

			$this->log_debug( __METHOD__ . '(): Entry already has a Intercom conversation associated to it. Skipping processing.' );
			return false;
		}

		// Prepare conversation data.
		$data = array(
			'email'       => $this->get_field_value( $form, $entry, $feed['meta']['customer_email'] ),
			'first_name'  => $this->get_field_value( $form, $entry, $feed['meta']['customer_first_name'] ),
			'last_name'   => $this->get_field_value( $form, $entry, $feed['meta']['customer_last_name'] ),
			'phone'       => $this->get_field_value( $form, $entry, $feed['meta']['customer_phone'] ),
			'body'        => \GFCommon::replace_variables( $feed['meta']['body'], $form, $entry ),
		);

		// If the email address is invalid, exit.
		if ( \GFCommon::is_invalid_or_empty_email( $data['email'] ) ) {

			$this->add_feed_error( esc_html__( 'Unable to create conversation because a valid email address was not provided.', 'gravityformsintercom' ), $feed, $entry, $form );
			return false;
		}

		// Loop through first and last name fields.
		foreach ( array( 'first_name', 'last_name' ) as $field_to_check ) {

			// If field value is longer than 40 characters, truncate.
			if ( strlen( $data[ $field_to_check ] ) > 40 ) {

				// Log that we are truncating field value.
				$this->log_debug( __METHOD__ . "(): Truncating {$field_to_check} field value because it is longer than maximum length allowed." );

				// Truncate value.
				$data[ $field_to_check ] = substr( $data[ $field_to_check ], 0, 40 );
			}
		}

		// If enabled, Process shortcodes for conversation body.
		if ( gf_apply_filters( 'gform_intercom_process_body_shortcodes', $form['id'], false, $form, $feed ) ) {
			$data['body'] = do_shortcode( $data['body'] );
		}

		try {

			$customer = $this->create_user_or_lead( $feed, $data );
			$message  = $this->create_message_for_customer( $customer, $data );

		} catch ( ClientException $e ) {

			// Log that customer was not created.
			$this->add_feed_error( 'Error creating user or conversation; ' . $e->getMessage(), $feed, $entry, $form );

			/**
			 * Fires when a conversation fails to post from form entry.
			 *
			 * @since 1.0
			 *
			 * @param array $feed feed data
			 * @param array $entry entry data
			 */
			do_action( 'gf_intercom_conversation_creation_failed', $feed, $entry );
			return false;
		}

		$conversation = $this->get_conversation_from_message( $message, $customer );

		// Add conversation ID to entry meta.
		gform_update_meta( $entry['id'], 'intercom_conversation_id', $conversation->id );

		// Add conversation user to entry meta.
		gform_update_meta( $entry['id'], 'intercom_conversation_user_id', $customer->id );

		/**
		 * Fires when an Intercom conversation has been successfully created.
		 *
		 * @since 1.0
		 *
		 * @param object $conversation Intercom conversation object
		 * @param object $customer Intercom user or lead object
		 */
		do_action( 'gf_intercom_conversation_created', $conversation, $customer );

		// Add notes and assignment if there's a note user.
		if ( ! rgempty( 'notes_admin', $feed['meta'] ) && rgars( $feed, 'meta/note' ) ) {

			// Prepare note contents.
			$note_text = \GFCommon::replace_variables( $feed['meta']['note'], $form, $entry );

			if ( gf_apply_filters( 'gform_intercom_process_note_shortcodes', $form['id'], false, $form, $feed ) ) {
				$note_text = do_shortcode( $note_text );
			}

			if ( empty( $note_text ) ) {
				return false;
			}

			// add a slight delay so the new convo is available
			usleep( 3000 );

			try {

				/**
				 * Filters the internal note text from feed settings.
				 *
				 * @since 1.0
				 *
				 * @param string $note_text
				 * @param object $conversation Intercom conversation object
				 * @param object $customer Intercom user or lead object
				 * @param array $feed feed data
				 */
				$note_text = apply_filters( 'gf_intercom_conversation_note', $note_text, $conversation, $customer, $feed );

				$this->add_internal_note( $conversation->id, $customer->id, $note_text, $feed );

			} catch ( ClientException $e ) {

				// Log that note was not added.
				$this->add_feed_error( 'Note was not added to conversation; ' . $e->getMessage(), $feed, $entry, $form );
				return false;
			}
		}
	}


	/**
	 * Creates a user or lead in Intercom per feed settings.
	 *
	 * @since 1.0
	 *
	 * @param array $feed feed info
	 * @param array $data the entry data
	 *
	 * @throws ClientException
	 * @return object $customer the created user or lead
	 */
	protected function create_user_or_lead( $feed, $data ) {

		$customer_info = [
			'email' => $data['email'],
			'phone' => $data['phone'],
			'name'  => "{$data['first_name']} {$data['last_name']}",
		];

		// Create or update Intercom user / lead
		if ( 'user' === rgars( $feed, 'meta/customer_type' ) ) {
			$customer = $this->api->users->create( $customer_info );
		} else {
			$customer = $this->api->leads->create( $customer_info );
		}

		return $customer;
	}


	/**
	 * Creates a new message for the given Intercom user or lead.
	 *
	 * @since 1.0
	 *
	 * @param object $customer the Intercom user or lead
	 * @param array $data entry data
	 *
	 * @throws ClientException
	 * @return object $message the created message
	 */
	protected function create_message_for_customer( $customer, $data ) {

		// Initialize conversation object.
		$message = $this->api->messages->create( [
			'message_type' => 'inapp',
			'body'         => str_replace( '&nbsp;', "\n", wp_strip_all_tags( $data['body'], true ) ), // no HTML! plain text only
			'from'         => [
				'type' => $customer->type,
				'id'   => $customer->id,
			],
		] );

		return $message;
	}


	/**
	 * Gets the conversation object associated with a message.
	 *
	 * You would think this method is really stupid, and you're right! Intercom decides to give you
	 * message objects (conversation parts) without ever telling you about the parent object for that item,
	 * just so you can experience the joy of sniffing out the parent conversation on your own ಠ_ಠ
	 *
	 * @param object $message the Intercom conversation part for the newly created message
	 * @return null|object intercom conversation object
	 */
	protected function get_conversation_from_message( $message, $customer ) {

		try {

			$list = $this->api->conversations->getConversations( [
				'type'             => 'user', // should not be 'lead', we'll get them for leads anyway with this
				'intercom_user_id' => $customer->id,
			] );

		} catch ( ClientException $e ) {

			$this->log_debug( __METHOD__ . '(): cannot retrieve conversations.' );
			return null;
		}

		// now find our winner!
		foreach ( $list->conversations as $conversation ) {

			if ( $conversation->conversation_message->id === $message->id ) {
				return $conversation;
			}
		}

		// we shouldn't get here, but just in case, we'll check for the chance we didn't get a matching object
		return null;
	}


	/**
	 * Adds a note to an existing conversation as a new reply.
	 *
	 * @since 1.0
	 *
	 * @param string $conversation_id the Intercom conversation ID
	 * @param string $customer_id the Intercom user or lead ID
	 * @param string $note_text the note body
	 * @param int|array $feed_or_wp_user the feed data or WP user to add the note
	 * @throws ClientException
	 */
	public function add_internal_note( $conversation_id, $customer_id, $note_text, $feed_or_wp_user ) {

		$params = [
			'type'             => 'admin',
			'message_type'     => 'note',
			'intercom_user_id' => $customer_id,
			'body'             => $note_text,
		];

		// try to correlate a WP user to Intercom admin, because for some reason Intercom doesn't think it makes
		// sense to let us access the current API user with access tokens ¯\_(ツ)_/¯
		if ( ! is_array( $feed_or_wp_user ) ) {

			$user = get_userdata( (int) $feed_or_wp_user );

			try {

				$admins = ! is_null( $this->admin_users ) ? $this->admin_users : $this->api->admins->getAdmins();

				// Loop through users.
				foreach ( $admins->admins as $admin ) {

					// Add user as choice and skip team users.
					if ( 'admin' === $admin->type && $admin->email === $user->user_email ) {
						$params['admin_id'] = $admin->id;
						break;
					}
				}

				// if we didn't find an admin to use, bail out
				if ( ! isset( $params['admin_id'] ) ) {
					$this->log_debug( __METHOD__ . '(): Could not add custom note to Intercom; no admin user found.' );
					return;
				}

			} catch ( ClientException $e ) {

				$this->log_debug( __METHOD__ . '(): Could not add custom note to Intercom.' );
				return;
			}

		// this means feed data was passed in, get the assignee and note author from that
		} else {

			$params['admin_id']    = $feed_or_wp_user['meta']['notes_admin'];
			$params['assignee_id'] = ! empty( $feed_or_wp_user['meta']['admin'] ) ? $feed_or_wp_user['meta']['admin'] : "0";
		}

		$this->api->conversations->replyToConversation( $conversation_id, $params );

		// Log that note was added.
		$this->log_debug( __METHOD__ . '(): Original body was successfully added to conversation as note.' );
	}



	// # HELPER METHODS ------------------------------------------------------------------------------------------------


	/**
	 * Initializes Intercom API if API credentials are valid.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFAddOn::get_plugin_setting()
	 * @uses GFAddOn::log_debug()
	 * @uses GFAddOn::log_error()
	 *
	 * @return bool|null
	 */
	public function initialize_api() {

		// If API library is already loaded, return.
		if ( ! is_null( $this->api ) ) {
			return true;
		}

		// Autoload vendor files.
		require_once 'vendor/autoload.php';

		// Load the API library.
		if ( ! class_exists( 'Intercom\IntercomClient' ) ) {
			require_once 'includes/api/src/IntercomClient.php';
		}

		// Get the Access Token.
		$access_token = $this->get_plugin_setting( 'access_token' );

		// If the Access Token is empty, do not run a validation check.
		if ( rgblank( $access_token ) ) {
			return null;
		}

		// Log that we're validating API credentials.
		$this->log_debug( __METHOD__ . '(): Validating access token.' );

		// Initialize a Intercom object with the API credentials.
		$intercom = new IntercomClient( $access_token, null );

		try {

			// Make a test request.
			$intercom->getRateLimitDetails();

			// Assign API object to class.
			$this->api = $intercom;

			// Log that test passed.
			$this->log_debug( __METHOD__ . '(): API credentials are valid.' );

			return true;

		} catch ( ClientException $e ) {

			// Log that test failed.
			$this->log_error( __METHOD__ . '(): API credentials are invalid; ' . $e->getMessage() );
			return false;
		}
	}


	/**
	 * Gets the IntercomClient API instance.
	 *
	 * @since 1.0
	 *
	 * @return \Intercom\IntercomClient
	 */
	public function get_api() {
		return $this->api;
	}


	/**
	 * Add the conversation ID entry meta property.
	 *
	 * @since  1.0
	 * @access public
	 * @param  array $entry_meta An array of entry meta already registered with the gform_entry_meta filter.
	 * @param  int $form_id The form id.
	 *
	 * @return array The filtered entry meta array.
	 */
	public function get_entry_meta( $entry_meta, $form_id ) {

		$entry_meta['intercom_conversation_id'] = [
			'label'             => __( 'Intercom Conversation ID', 'gravityformsintercom' ),
			'is_numeric'        => true,
			'is_default_column' => false,
		];

		return $entry_meta;
	}


}

