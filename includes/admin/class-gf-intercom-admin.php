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
 * @copyright Copyright (c) 2018, rocketgenius and 2018, SkyVerge, Inc
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\GravityForms\Intercom;

defined( 'ABSPATH' ) or exit;

use GuzzleHttp\Exception\GuzzleException as Exception;

/**
 * The Intercom add-on admin class.
 *
 * @since 1.0
 */
class Admin {


	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $slug = null;


	/**
	 * Admin class constructor.
	 *
	 * @since 1.0
	 *
	 * @param string $slug plugin slug
	 */
	public function __construct( $slug ) {

		$this->slug = $slug;

		$this->add_hooks();
	}


	/**
	 * Adds class hooks.
	 *
	 * @since 1.0
	 */
	protected function add_hooks() {

		// entry meta box
		if ( gf_intercom()->is_gravityforms_supported( '2.0-beta-3' ) ) {
			add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'register_meta_box' ), 10, 3 );
		} else {
			add_action( 'gform_entry_detail_sidebar_middle', array( $this, 'add_entry_detail_panel' ), 10, 2 );
		}

		add_action( 'admin_init', array( $this, 'maybe_create_conversation' ) );

		// add Intercom notes from entries
		add_filter( 'gform_addnote_button', array( $this, 'add_note_checkbox' ) );
		add_action( 'gform_post_note_added', array( $this, 'add_note_to_conversation' ), 10, 6 );

		// entries list
		add_filter( 'gform_entries_column_filter', array( $this, 'add_entry_conversation_id_column' ), 10, 5 );
		add_filter( 'gform_entry_list_bulk_actions', array( $this, 'add_bulk_action' ), 10, 2 );
		add_action( 'gform_entry_list_action_intercom', array( $this, 'process_bulk_action' ), 10, 3 );
	}


	/** Entry details *******************************************/


	/**
	 * Add a panel to the entry view with details about the Intercom conversation.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $form The current Form object.
	 * @param array $entry The current Entry object.
	 *
	 * @uses \GFFeedAddOn::get_active_feeds()
	 * @uses GFIntercom::get_panel_markup()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return string
	 */
	public function add_entry_detail_panel( $form, $entry ) {

		// If the API isn't initialized, return.
		if ( ! gf_intercom()->get_active_feeds( $form['id'] ) || ! gf_intercom()->get_api() ) {
			return;
		}

		$html = '<div id="intercomdiv" class="stuffbox">';
		$html .= '<h3 class="handle" style="cursor:default;"><span>' . esc_html__( 'Intercom Details', 'gravityformsintercom' ) . '</span></h3>';
		$html .= '<div class="inside">';
		$html .= $this->get_panel_markup( $form, $entry );
		$html .= '</div>';
		$html .= '</div>';

		echo $html;
	}


	/**
	 * Add the Intercom details meta box to the entry detail page.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @param array $meta_boxes The properties for the meta boxes.
	 * @param array $entry The entry currently being viewed/edited.
	 * @param array $form The form object used to process the current entry.
	 *
	 * @uses \GFFeedAddOn::get_active_feeds()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return array
	 */
	public function register_meta_box( $meta_boxes, $entry, $form ) {

		if ( gf_intercom()->get_active_feeds( $form['id'] ) && gf_intercom()->initialize_api() ) {

			$meta_boxes[ $this->slug ] = array(
				'title'    => esc_html__( 'Intercom Details', 'gravityformsintercom' ),
				'callback' => array( $this, 'add_details_meta_box' ),
				'context'  => 'side',
			);
		}

		return $meta_boxes;
	}


	/**
	 * The callback used to echo the content to the meta box.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $args An array containing the form and entry objects.
	 *
	 * @uses Admin::get_panel_markup()
	 */
	public function add_details_meta_box( $args ) {

		echo $this->get_panel_markup( $args['form'], $args['entry'] );
	}


	/**
	 * Generate the markup for use in the meta box.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $form The current Form object.
	 * @param array $entry The current Entry object.
	 *
	 * @uses \GFAddOn::log_error()
	 * @uses \GFCommon::format_date()
	 * @uses GFIntercom::get_entry_conversation_id()
	 *
	 * @return string
	 */
	public function get_panel_markup( $form, $entry ) {

		// Initialize HTML string.
		$html = '';

		// Get conversation ID.
		$conversation_id = $this->get_entry_conversation_id( $entry );

		// If a Intercom conversation exists, display conversation details.
		if ( $conversation_id ) {

			try {

				// Get conversation.
				$conversation = gf_intercom()->get_api()->conversations->getConversation( $conversation_id );

			} catch ( Exception $e ) {

				// Delete conversation ID from entry.
				gform_delete_meta( $entry['id'], 'intercom_conversation_id' );

				// Log that conversation could not be retrieved.
				$this->log_error( __METHOD__ . '(): Could not get Intercom conversation; ' . $e->getMessage() );

				return '';
			}

			// it would be nice if we could link to a convo, but there's no canonical URLs for conversations in Intercom ¯\_(ツ)_/¯
			$html .= '<a href="https://app.intercom.io" target="_blank">' . esc_html__( 'Open Intercom', 'gravityformsintercom' ) . '&rarr;</a><br /><br />';
			$html .= esc_html__( 'Conversation ID', 'gravityformsintercom' ) . ': <span>' . $conversation->id . '</span><br /><br />';
			$html .= esc_html__( 'Status', 'gravityformsintercom' ) . ': ' . ucwords( $conversation->state ) . '<br /><br />';
			$html .= esc_html__( 'Created At', 'gravityformsintercom' ) . ': ' . date_i18n( 'Y-m-d H:i:s', $conversation->created_at ) . '<br /><br />';
			$html .= esc_html__( 'Last Updated At', 'gravityformsintercom' ) . ': ' . date_i18n( 'Y-m-d H:i:s', $conversation->updated_at ) . '<br /><br />';

		} else {

			// Get create conversation URL.
			$url = add_query_arg( array(
				'gf_intercom' => 'process',
				'lid'         => $entry['id']
			) );

			// Display create conversation button.
			$html .= '<a href="' . esc_url( $url ) . '" class="button">' . esc_html__( 'Create Conversation', 'gravityformsintercom' ) . '</a>';
		}

		return $html;
	}


	/**
	 * Insert "Add Note to Intercom Conversation" checkbox to add note form.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $note_button Add note button.
	 *
	 * @uses \GFAddOn::get_current_entry()
	 * @uses GFIntercom::get_entry_conversation_id()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return string $note_button
	 */
	public function add_note_checkbox( $note_button ) {

		// Get current entry.
		$entry = $this->get_current_entry();

		// If API is not initialized or entry does not have a Help Scout conversation ID, return existing note button.
		if ( ! gf_intercom()->initialize_api() || is_wp_error( $entry ) || ! $this->get_entry_conversation_id( $entry ) ) {
			return $note_button;
		}

		$note_button .= '<span style="float:right;line-height:28px;">';
		$note_button .= '<input type="checkbox" name="intercom_add_note" value="1" id="gform_intercom_add_note" style="margin-top:0;" ' . checked( rgpost( 'intercom_add_note' ), '1', false ) . ' /> ';
		$note_button .= '<label for="gform_intercom_add_note">' . esc_html__( 'Add Note to Intercom Conversation', 'gravityformsintercom' ) . '</label>';
		$note_button .= '</span>';

		return $note_button;
	}


	/**
	 * Create Intercom creation on the entry view page.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses \GFAddOn::get_current_entry()
	 * @uses \GFAPI::get_form()
	 * @uses \GFFeedAddOn::maybe_process_feed()
	 * @uses GFIntercom::get_entry_conversation_id()
	 */
	public function maybe_create_conversation() {

		// If we're not on the entry view page, return.
		if ( rgget( 'page' ) !== 'gf_entries' || rgget( 'view' ) !== 'entry' || rgget( 'gf_intercom' ) !== 'process' ) {
			return;
		}

		// Get the current form and entry.
		$form  = \GFAPI::get_form( rgget( 'id' ) );
		$entry = $this->get_current_entry();

		// If a Intercom conversation ID exists for this entry, return.
		if ( $this->get_entry_conversation_id( $entry ) ) {
			return;
		}

		// Process feeds only if we've clicked "Create conversation".
		// rgget() seems to be inconsistent here, so let's double-check before processing
		if ( isset( $_GET['gf_intercom'] ) && 'process' === $_GET['gf_intercom'] ) {
			gf_intercom()->maybe_process_feed( $entry, $form );
		}
	}


	/**
	 * Add note to Intercom conversation.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param int    $note_id   The ID of the created note.
	 * @param int    $entry_id  The ID of the entry the note belongs to.
	 * @param int    $user_id   The ID of the user who created the note.
	 * @param string $user_name The name of the user who created the note.
	 * @param string $note      The note contents.
	 * @param string $note_type The note type.
	 *
	 * @uses \GFAddOn::log_debug()
	 * @uses \GFAddOn::log_error()
	 * @uses \GFAPI::get_entry()
	 * @uses GFIntercom::get_entry_conversation_id()
	 * @uses GFIntercom::initialize_api()
	 */
	public function add_note_to_conversation( $note_id, $entry_id, $user_id, $user_name, $note, $note_type ) {

		// If add note checkbox not selected, return.
		if ( rgpost( 'intercom_add_note' ) !== '1' ) {
			return;
		}

		// Get entry.
		$entry = \GFAPI::get_entry( $entry_id );

		// Get conversation ID.
		$conversation_id = $this->get_entry_conversation_id( $entry );

		// Get Intercom user ID.
		$intercom_user_id = rgar( $entry, 'intercom_conversation_user_id' );

		// If API is not initialized or entry does not have an Intercom conversation ID, exit.
		if ( ! gf_intercom()->get_api() || ! $conversation_id ) {
			return;
		}

		try {

			// Post note to conversation.
			gf_intercom()->add_internal_note( $conversation_id, $intercom_user_id, $note, $user_id );

			// Log that note was added.
			gf_intercom()->log_debug( __METHOD__ . '(): Note was successfully added to conversation.' );

		} catch ( Exception $e ) {

			// Log that note was not added.
			gf_intercom()->log_error( __METHOD__ . '(): Note was not added to conversation; ' . $e->getMessage() );

		}

	}


	/** Entry columns *******************************************/


	/**
	 * Add Intercom conversation link to entry list column.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $value Current value that will be displayed in this cell.
	 * @param int $form_id ID of the current form.
	 * @param int $field_id ID of the field that this column applies to.
	 * @param array $entry Current entry object.
	 * @param string $query_string Current page query string with search and pagination state.
	 *
	 * @uses \GFAddOn::log_error()
	 * @uses GFIntercom::get_entry_conversation_id()
	 * @uses GFIntercom::initialize_api()
	 *
	 * @return string
	 */
	public function add_entry_conversation_id_column( $value, $form_id, $field_id, $entry, $query_string ) {

		// If this is not the Intercom Conversation ID column, return value.
		if ( 'intercom_conversation_id' !== $field_id ) {
			return $value;
		}

		// Get conversation ID.
		return $this->get_entry_conversation_id( $entry );
	}


	/**
	 * Add Create Conversation to entry list bulk actions.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $actions Bulk actions.
	 * @param string|int $form_id The current form ID.
	 *
	 * @return array
	 */
	public function add_bulk_action( $actions = array(), $form_id = '' ) {

		// Add action.
		$actions['intercom'] = esc_html__( 'Create Intercom Conversation', 'gravityformsintercom' );

		return $actions;
	}


	/**
	 * Process Intercom entry list bulk actions.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $action Action being performed.
	 * @param array $entries The entry IDs the action is being applied to.
	 * @param string|int $form_id The current form ID.
	 *
	 * @uses \GFAPI::get_entry()
	 * @uses \GFAPI::get_form()
	 * @uses \GFFeedAddOn::maybe_process_feed()
	 * @uses GFIntercom::get_entry_conversation_id()
	 */
	public function process_bulk_action( $action = '', $entries = array(), $form_id = '' ) {

		// If no entries are being processed, return.
		if ( empty( $entries ) ) {
			return;
		}

		// Get the current form.
		$form = \GFAPI::get_form( $form_id );

		// Loop through entries.
		foreach ( $entries as $entry_id ) {

			// Get the entry.
			$entry = \GFAPI::get_entry( $entry_id );

			// If a Intercom conversation ID exists for this entry, skip.
			if ( $this->get_entry_conversation_id( $entry ) ) {
				continue;
			}

			// Process feeds.
			gf_intercom()->maybe_process_feed( $entry, $form );
		}
	}


	/** Helper methods *******************************************/


	/**
	 * Helper function to get current entry.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFAddOn::is_gravityforms_supported()
	 * @uses GFAPI::get_entries()
	 * @uses GFAPI::get_entry()
	 * @uses GFCommon::get_base_path()
	 * @uses GFEntryDetail::get_current_entry()
	 *
	 * @return array $entry
	 */
	public function get_current_entry() {

		if ( gf_intercom()->is_gravityforms_supported( '2.0-beta-3' ) ) {

			if ( ! class_exists( '\\GFEntryDetail' ) ) {
				require_once( \GFCommon::get_base_path() . '/entry_detail.php' );
			}

			return \GFEntryDetail::get_current_entry();

		} else {

			$entry_id = rgpost( 'entry_id' ) ? absint( rgpost( 'entry_id' ) ) : absint( rgget( 'lid' ) );

			if ( $entry_id > 0 ) {

				return \GFAPI::get_entry( $entry_id );

			} else {

				$position = rgget( 'pos' ) ? rgget( 'pos' ) : 0;
				$paging   = [
					'offset'    => $position,
					'page_size' => 1,
				];

				$entries  = \GFAPI::get_entries( rgget( 'id' ), array(), null, $paging );

				return $entries[0];
			}
		}
	}


	/**
	 * Retrieve the conversation id for the current entry.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $entry The entry currently being viewed/edited.
	 *
	 * @return string
	 */
	public function get_entry_conversation_id( $entry ) {

		// Define entry meta key.
		$key = 'intercom_conversation_id';

		// Get conversation ID.
		$id = rgar( $entry, $key );

		if ( empty( $id ) && rgget( 'gf_intercom' ) === 'process' ) {
			$id = gform_get_meta( $entry['id'], $key );
		}

		return $id;
	}


}
