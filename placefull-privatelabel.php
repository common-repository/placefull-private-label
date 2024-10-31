<?php
/*
Plugin Name: PlaceFull Private Label
Plugin URI: https://my.placefull.com
Description: Display your <a href="http://placefull.com">PlaceFull</a> listings directly on your WordPress website.  Not a PlaceFull merchant?  Check out what PlaceFull's online booking services can do for your business by visiting <a href="https://my.placefull.com">my.placefull.com</a>.  To adjust the settings of this plugin, visit the PlaceFull page under your WordPress Settings menu.
Version: 1.1
Author: PlaceFull
Author URI: http://placefull.com
License: GPLv3
*/

// [placefull]
function placefull_init($atts){

	$options = get_option( 'placefull_options' );
	if($options != false)
	{
		$account_id = $options['id_number'];

		$output = '<script type="text/javascript">window.onload = function() {var scriptTag = document.createElement("script");scriptTag.src = "REPLACE_ME";scriptTag.type = "text/javascript";document.body.appendChild(scriptTag);}</script>';
		$output .= '<div id="pf-root"></div>';

		return $output;
	}
	return null;
}

add_shortcode('placefull', 'placefull_init');

// plugin activation hooks
register_activation_hook(__FILE__, 'placefull_plugin_activation');
add_action('admin_init', 'placefull_plugin_redirect');

function placefull_plugin_activation() {
    add_option('placefull_plugin_do_activation_redirect', true);
}

function placefull_plugin_redirect() {
    if (get_option('placefull_plugin_do_activation_redirect', false)) {
        delete_option('placefull_plugin_do_activation_redirect');
        wp_redirect('options-general.php?page=placefull-settings-admin');
    }
}

// plugin settings page
class PlaceFullSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'PlaceFull',
            'PlaceFull',
            'manage_options',
            'placefull-settings-admin',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'placefull_options' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>PlaceFull Settings</h2>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'placefull_option_group' );
                do_settings_sections( 'placefull-settings-page' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'placefull_option_group', // Option group
            'placefull_options', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'PlaceFull Plugin Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'placefull-settings-page' // Page
        );

        add_settings_field(
            'id_number', // ID
            'Account ID', // Title
            array( $this, 'id_number_callback' ), // Callback
            'placefull-settings-page', // Page
            'setting_section_id' // Section
        );
/*
        add_settings_field(
            'title',
            'Street Cred',
            array( $this, 'title_callback' ),
            'placefull-settings-page',
            'setting_section_id'
        );
*/
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['id_number'] ) )
            $new_input['id_number'] = sanitize_text_field( $input['id_number'] );

        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        ?>
            <div class="updated">
                <p>
                    To finish setting up the PlaceFull plugin, please enter your PlaceFull Account ID below.
                    Your PlaceFull Account ID can be found by logging in to your <a href="https://my.placefull.com/My/Dashboard">PlaceFull account</a> and accessing the "Publish" screen.
                    Select the WordPress option to find your Account ID.
                </p>
            </div>
            <p>
                Once you've entered and saved your Account ID, create or edit the WordPress page that should display your PlaceFull listings and enter the following text (including the brackets): <em>[placefull]</em>
            </p>
        <?php
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function id_number_callback()
    {
        printf(
            '<input type="text" id="id_number" name="placefull_options[id_number]" value="%s" />',
            isset( $this->options['id_number'] ) ? esc_attr( $this->options['id_number']) : ''
        );
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function title_callback()
    {
        printf(
        	'<input type="checkbox" id="title" name="placefull_options[title]" value="1"' . checked( 1, $this->options['title'], false ) . '/> &nbsp;Show off that PlaceFull powers your online booking!'
        );
    }
}

if( is_admin() )
    $my_settings_page = new PlaceFullSettingsPage();

?>
