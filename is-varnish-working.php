<?php

/**
 *
 * @link              https://oliverwhysall.co.uk
 * @since             1.0.2
 * @package           Is_Varnish_Working
 *
 * @wordpress-plugin
 * Plugin Name:       Is Varnish Working?
 * Plugin URI:        https://isvarnishworking.co.uk
 * Description:       Uses your wordpress url to search the http headers to test if varnish cache is working
 * Version:           1.0.2
 * Author:            Oliver Whysall
 * Author URI:        https://oliverwhysall.co.uk
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       is-varnish-working
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'IS_VARNISH_WORKING_VERSION', '1.0.2' );


class IsVarnishWorking {
    private $is_varnish_working__options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'is_varnish_working__add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'is_varnish_working__page_init' ) );
    }

    public function is_varnish_working__add_plugin_page() {
        add_options_page(
            'Is Varnish Working?', // page_title
            'Is Varnish Working?', // menu_title
            'manage_options', // capability
            'is-varnish-working', // menu_slug
            array( $this, 'is_varnish_working__create_admin_page' ) // function
        );
    }

    public function is_varnish_working__create_admin_page() {
        $this->is_varnish_working__options = get_option( 'is_varnish_working__option_name' ); ?>

        <style>
            .wrapper {
                display: grid;
                grid-template-columns: 32% 32% 32%;
                grid-gap: 10px;
            }

            .box {
                background-color: #CCC;
                color: #fff;
                border-radius: 5px;
                padding: 20px;
            }
            .box h1 {
                line-height: 1.5em;
                text-align: center;
            }
            .headersList li {
                list-style: disc;
                margin-left: 20px;
            }

            .alert {
                position: relative;
                padding: .75rem 1.25rem;
                margin-bottom: 1rem;
                border: 1px solid transparent;
                border-radius: .25rem;
            }

            .alert-danger {
                color: #721c24;
                background-color: #f8d7da;
                border-color: #f5c6cb;
            }

        </style>
        <div class="">
            <h2>Is Varnish Working?</h2>
            <p>Check HTTP headers to test for Varnish Cache headers</p>
            <p>How this works: This page uses the API available at <a href="https://isvarnishworking.co.uk/api">isvarnishworking.co.uk/api</a> to determine if the URL has the tell tale Varnish Cache headers in the response.</p>
            <?php settings_errors(); ?>
            <div id="api_error"></div>
            <hr />
            <form id="api_test_form">
                URL: <input type="text" placeholder="Enter URL to test" id="api_test" name="url" value="<?php echo get_site_url(); ?>" class="regular-text"/>
                <input type="submit" value="Test URL" />
                <input type="button" id="api_this_site" value="Test This Site" />
            </form>
            <hr />
            <div class="wrapper">
                <div id="api_response_varnish" class="box"><span class="spinner is-active" style="float:left;"></span></div>
                <div id="api_response_cache" class="box"></div>
                <div id="api_response_other" class="box"></div>
            </div>
            <hr />
            <h2>All HTTP Headers Received:</h2>
            <div id="api_response_headers"><span class="spinner is-active" style="float:left;"></span></div>

            <script>
                jQuery(function($){
                    $(document).ready(function() {
                        $("#api_this_site").click(function (e) {
                            e.preventDefault();
                            $('#api_test').val("<?php echo get_site_url(); ?>");
                            $('#api_response_varnish').html("<span class='spinner is-active' style='float:left;'></span>");
                            $('#api_response_cache').html("");
                            $('#api_response_other').html("");
                            $('#api_response_headers').html("<span class='spinner is-active' style='float:left;'></span>");
                            form = "url=<?php echo urlencode(get_site_url()); ?>";
                            test_url(form);
                            return false;
                        });
                        $("#api_test_form").submit(function (e) {
                            e.preventDefault();
                            $('#api_response_varnish').html("<span class='spinner is-active' style='float:left;'></span>");
                            $('#api_response_cache').html("");
                            $('#api_response_other').html("");
                            $('#api_response_headers').html("<span class='spinner is-active' style='float:left;'></span>");
                            var form = $(this).serialize();
                            test_url(form);
                            return false;
                        });

                        form = "url=<?php echo urlencode(get_site_url()); ?>&showall=true";
                        test_url(form);

                        function test_url(form) {
                            $.ajax({
                                type: "GET",
                                url: 'https://isvarnishworking.co.uk/api/url-test.php',
                                data: form + "&showall=true&source=wp-plugin",
                                success: function (data) {
                                    $(".spinner").removeClass("is-active");
                                    // data is ur summary
                                    var varnish;
                                    var cache;
                                    var other;
                                    var allHeaders;
                                    var error;

                                    if (data.success == false) {
                                        var error = data.error;
                                        $('#api_error').show();
                                        $('#api_error').html("<div class='alert alert-danger'>" + error + "</div>");
                                        $('#api_response_varnish').hide();
                                        $('#api_response_cache').hide();
                                        $('#api_response_other').hide();
                                        $('#api_response_headers').hide();
                                    } else {
                                        $('#api_error').hide();
                                        $('#api_response_varnish').show();
                                        $('#api_response_cache').show();
                                        $('#api_response_other').show();
                                        $('#api_response_headers').show();

                                        allHeaders = "<ul class='headersList'>";
                                        for (var item of data.allHeaders)  {
                                            allHeaders = allHeaders + "<li>" + item + "</li>";
                                        }
                                        allHeaders = allHeaders + "</ul>";
                                        if (data.headers.varnish == true) {
                                            varnish = "<h1>✅<br />X-Varnish Header Found</h1>";
                                        } else {
                                            varnish = "<h1>❌<br />No X-Varnish Header Found</h1>";
                                        }
                                        if (data.headers.cache == true) {
                                            cache = "<h1>✅<br />X-Cache Header Found</h1>";
                                        } else {
                                            cache = "<h1>❌<br />No X-Cache Header Found</h1>";
                                        }
                                        if (data.headers.other == true) {
                                            other = "<h1>✅<br />Other Varnish Headers Found</h1>";
                                        } else {
                                            other = "<h1>❌<br />No Other Varnish Headers Found</h1>";
                                        }
                                        $('#api_response_varnish').html(varnish);
                                        $('#api_response_cache').html(cache);
                                        $('#api_response_other').html(other);
                                        $('#api_response_headers').html(allHeaders);
                                    }
                                }
                            });
                        }
                    });
                });
            </script>

        </div>
    <?php }

    public function is_varnish_working__page_init() {
        register_setting(
            'is_varnish_working__option_group', // option_group
            'is_varnish_working__option_name', // option_name
            array( $this, 'is_varnish_working__sanitize' ) // sanitize_callback
        );

        add_settings_section(
            'is_varnish_working__setting_section', // id
            'Settings', // title
            array( $this, 'is_varnish_working__section_info' ), // callback
            'is-varnish-working-admin' // page
        );

        add_settings_field(
            'check_url_0', // id
            'Check URL', // title
            array( $this, 'check_url_0_callback' ), // callback
            'is-varnish-working-admin', // page
            'is_varnish_working__setting_section' // section
        );
    }

    public function is_varnish_working__sanitize($input) {
        $sanitary_values = array();
        if ( isset( $input['check_url_0'] ) ) {
            $sanitary_values['check_url_0'] = sanitize_text_field( $input['check_url_0'] );
        }

        return $sanitary_values;
    }

    public function is_varnish_working__section_info() {

    }

    public function check_url_0_callback() {
        printf(
            '<input class="regular-text" type="text" name="is_varnish_working__option_name[check_url_0]" id="check_url_0" value="%s">',
            isset( $this->is_varnish_working__options['check_url_0'] ) ? esc_attr( $this->is_varnish_working__options['check_url_0']) : ''
        );
    }

}
if ( is_admin() )
    $is_varnish_working_ = new IsVarnishWorking();


function is_varnish_working_admin_js() {
    wp_register_script('is_varnish_working_admin_js', plugins_url('is-varnish-working.js', __FILE__),  array('jquery'));
    wp_enqueue_script('is_varnish_working_admin_js');
}
add_action('admin_enqueue_scripts', 'is_varnish_working_admin_js');