<?php
/**
 * Plugin Name:         SustainabilityTracker
 * Description:         The Sustainability Tracker Plugin helps monitor and reduce website carbon footprints. It integrates with custom tracking solutions or Google Analytics to collect page views and calculate carbon emissions. It also includes a Carbon Footprint Calculator for estimating impact based on monthly traffic. Ideal for making your website more eco-friendly. 
 * Author:              Mo Zaidan
 * Author URI:          https://github.com/zaidan1 
 * Plugin URI:          https://github.com/zaidan1/sustainability-tracker
 * License:             GPLv2 or later 
 * License URI:         https://github.com/zaidan1/sustainability-tracker/blob/main/LICENSE
 * Version:             1.2.3
 * Requires at least:   5.4
 * Requires PHP:        5.6
 *
 * 
 */

/**
 * Plugin main file
 */


require_once __DIR__ . '/vendor/autoload.php';

use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportRequest;
// Exit if accessed directly
if (!defined('ABSPATH')) 
{
    exit;
}

// Define constants
define('ST_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ST_PLUGIN_URL', plugin_dir_url(__FILE__));
// Include necessary files
include_once(ST_PLUGIN_DIR . 'includes/carbon-footprint-calculator.php');
include_once(ST_PLUGIN_DIR . 'includes/sustainable-hosting-report.php');
include_once(ST_PLUGIN_DIR . 'includes/integration-green-initiatives.php');
include_once(ST_PLUGIN_DIR . 'includes/annual_impact.php');
include_once(ST_PLUGIN_DIR . 'includes/rating.php');
include_once(ST_PLUGIN_DIR . 'includes/rating_function.php');

add_action('admin_enqueue_scripts', 'st_enqueue_scripts');
add_action('admin_enqueue_style', 'st_enqueue_scripts');
function st_add_settings_link($links)
{
    $settings_link = '<a href="admin.php?page=sustainability-tracker-settings">Settings</a>';
    array_push($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'st_add_settings_link');

function st_enqueue_scripts() 
{
    // Füge die Versionsnummer für das Stylesheet hinzu
    wp_enqueue_style(
        'st-styles', 
        ST_PLUGIN_URL . 'assets/css/st-style.css', 
        array(), 
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/st-style.css') // Verwende den Datei-Timestamp als Versionsnummer
    );

    wp_enqueue_script(
        'st-scripts', 
        plugins_url('/assets/js/st-script.js', __FILE__), 
        array('jquery'), 
        '1.0', 
        true 
    );

    wp_enqueue_script(
        'chart-js', 
        'https://cdn.jsdelivr.net/npm/chart.js', 
        array(), 
        '3.7.1', 
        true 
    );
     wp_localize_script('st-scripts', 'st_ajax_object', array(
        'st_nonce' => wp_create_nonce('st_calculate_carbon_footprint_nonce')
    ));
}


// Register activation hook
register_activation_hook(__FILE__, 'st_create_post_views_table');

// Function to create custom tables
function st_create_post_views_table() 
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'st_custom_track_post_views';

    // Prüfe, ob die Tabelle bereits existiert
    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) 
    {
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id bigint(20) UNSIGNED NOT NULL,
            view_date datetime NOT NULL,
            user_id bigint(20) UNSIGNED DEFAULT 0,
            PRIMARY KEY  (id),
            KEY post_id (post_id),
            KEY view_date (view_date),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Lade das Upgrade-API für dbDelta
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

function st_add_admin_message($message,$stat) 
{
    
    $messages = get_transient('st_'.$stat.'_messages');
    if (!is_array($messages)) 
    {
        $messages = array();
    }
    $messages[] = $message;
    $st_message="st_$stat"."_messages";
    set_transient('st_notice_'.$stat, true); // Expires in 30 seconds
    set_transient($st_message, $messages); // Expires in 30 seconds
}
add_action('st_add_admin_message', 'st_add_admin_message');

////////////////////////////////////////////////////////////
add_action('admin_menu', 'st_register_admin_pages');
function st_admin_dashboard() 
{

    // Bestimmen der Page Views basierend auf der ausgewählten Tracking-Lösung
    $tracking_solution = get_option('st_tracking_solution');
    switch ($tracking_solution) 
    {
        case 'google_analytics':
            $page_views = st_get_page_views_from_google_analytics(false);
	    $total_page_views=st_get_page_views_from_google_analytics(true);
            break;
        case 'custom':
        default:
            $page_views = st_get_custom_page_views(false);
	    $total_page_views=st_get_custom_page_views(true);
            break;
    }
    

    // Berechnung des Carbon Footprint und des Carbon Offset
    $total_carbon_footprint = st_calculate_carbon_footprint($total_page_views);
    $carbon_footprint = st_calculate_carbon_footprint($page_views);
    $carbon_offset = st_get_carbon_offset($carbon_footprint);
    $total_carbon_offset = st_get_carbon_offset($total_carbon_footprint);

    $st_carbon_calculator=st_carbon_footprint_calculator($page_views);
    $st_green_initiatives=st_integration_green_initiatives();
    $st_hosting_report=st_sustainable_hosting_report($total_carbon_footprint,$total_carbon_offset,$total_page_views);
    $st_annual_impact=st_annual_Impact_initiatives($page_views,$carbon_footprint);
    $reating_function=rating_function($carbon_footprint,$carbon_offset);
    $rating_overview=rating($page_views,$carbon_footprint,$carbon_offset,$reating_function);
    ?>
    <div class="wrap">
        <h1>Sustainability Tracker Dashboard</h1>
        <?php
        if (get_transient('st_notice_error')) 
        {
            $messages = get_transient('st_error_messages');
            ?>
            <div class="notice notice-error is-dismissible">
                <?php
                foreach ($messages as $message) {
                    echo '<p>' . esc_html($message) . '</p>';
                }
                ?>
            </div>
            <?php
            delete_transient('st_notice_error');
            delete_transient('st_error_messages');
        }
	if(get_transient('st_notice_warning'))
 	{
	  $messages = get_transient('st_warning_messages');
            ?>
            <div class="notice notice-warning is-dismissible">
                <?php
                foreach ($messages as $message) {
                    echo '<p>' . esc_html($message) . '</p>';
                }
                ?>
            </div>
            <?php
            delete_transient('st_notice_warning');
            delete_transient('st_warning_messages');

	}
        ?>
        <p>Welcome to the Sustainability Tracker Dashboard. Here you can monitor and manage your website's environmental impact.</p>
        <hr>
        <div class="st-dashboard">
            <!-- Overview Section -->
		<?php echo $rating_overview;?>
		
            <!-- Annual Impact Section -->
		<?php echo $st_annual_impact;?>
	  
            <!-- Carbon Footprint Calculator Section -->
		<?php echo $st_carbon_calculator;?>
            <!-- Sustainable Hosting Report Section -->
		<?php echo $st_hosting_report; ?>
            <!-- Green Initiatives Integration Section -->
                <?php echo $st_green_initiatives; ?>
        </div>
    </div>
<script>
    // Initialize the Chart.js variable
    var mychar;

    jQuery(document).ready(function($) 
    {
        var rating = "<?php echo $reating_function; ?>"; // This should be dynamically calculated
        var ratingElement = document.getElementById("carbon-rating");
        var ratingIndicator = document.getElementById("rating-indicator");
        var globalAverageElement = document.getElementById("global-average");

        ratingElement.textContent = rating;

        var ratingLevels = 
	{
            "A+": 0,
            "A": 1,
            "B": 2,
            "C": 3,
            "D": 4,
            "E": 5,
            "F": 6
        };

        var ratingPosition = ratingLevels[rating] * 100 / 7;
        ratingIndicator.style.left = "calc(" + ratingPosition + "% - -30px)"; // Center the indicator

        // Initial Chart.js setup
        const ctx = document.getElementById('carbonFootprintChart').getContext('2d');
        mychar = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Carbon Footprint and Carbon Offset'],
                datasets: [
                    {
                        label: 'Carbon Footprint (kg CO₂)',
                        data: [<?php echo number_format($carbon_footprint,3); ?>],
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Carbon Offset cost',
                        data: [<?php echo number_format($carbon_offset,3); ?>],
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Update chart configuration by mutating
        function updateConfigByMutating(chart) {
            chart.options.plugins.title.text = 'Updated Carbon Footprint and Offset Costs';
            chart.update();
        }

        // Update chart configuration by creating a new config object
        function updateConfigAsNewObject(chart) {
            chart.options = {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Updated Carbon Footprint and Offset Costs'
                    }
                },
                scales: {
                    x: {
                        display: true
                    },
                    y: {
                        display: true,
                        beginAtZero: true
                    }
                }
            };
            chart.update();
        }
        function removeData(chart) 
	{
            chart.data.labels.pop();
            chart.data.datasets.forEach((dataset) => {
                dataset.data.pop();
            });
            chart.update();
        }

        $('#st-carbon-footprint-form').on('submit', function(e) {
            e.preventDefault();
            var pageViews = $('#page-views').val();

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'st_carbon_footprint',
                    st_nonce: st_ajax_object.st_nonce,
                    page_views: pageViews
                },
                success: function(response) 
		{
                    document.getElementById('st-carbon-footprint-result').innerText = 'Estimated Carbon Footprint: ' + parseFloat(response.data.carbon_footprint).toFixed(3) + ' kg CO₂ ';

                    // Add new data to the chart
                    mychar.data.datasets[0].data.push( parseFloat(response.data.carbon_footprint).toFixed(3) );
                    mychar.data.datasets[1].data.push( parseFloat(response.data.carbon_offset).toFixed(3) );

                    // Optionally add a new label (e.g., for new submissions)
                    mychar.data.labels.push('New Data ' + (mychar.data.labels.length + 1));

                    // Update the chart title using one of the update functions
                    updateConfigByMutating(mychar);  // Or use updateConfigAsNewObject(mychar);
                }
            });
        });
    });
</script>


    <?php
}




//////////////////////////

function st_calculate_carbon_footprint($page_views) 
{
    $average_co2_per_view = 0.0002; // Example value in kg CO₂
    return $page_views * $average_co2_per_view;
}

//////////////////////////////
function st_get_carbon_offset($carbon_footprint) 
{
  // $10 per ton of CO2 offset (1000 kg)
    $cost_per_ton = 10;
    $carbon_offset_cost = ($carbon_footprint / 1000) * $cost_per_ton;

    return $carbon_offset_cost; // in dollars
}

//////////////////////////////
function st_register_settings() 
{
    register_setting('st_settings_group', 'st_tracking_solution');
    register_setting('st_settings_group', 'st_feed_urls');
    register_setting('st_settings_group', 'st_enable_rss_feed');
    register_setting('st_settings_group', 'st_rss_feeds_shuffle');
    register_setting('st_settings_group', 'st_google_analytics_property_id');
    register_setting('st_settings_group', 'st_display_position');
    register_setting('st_settings_group', 'st_display_pages');
#    register_setting('st_settings_group', 'st_jetpack_api_key');
 #   register_setting('st_settings_group', 'st_google_credentials_json');
 #   register_setting('st_settings_group', 'st_carbon_offset_api_key');
    add_settings_section(
        'st_feed_settings_section',
        'RSS Feed Settings',
        null,
        'st_settings_group'
    );


    add_settings_field(
        'st_enable_rss_feed',
        'Enable RSS Feed Integration',
        'st_enable_rss_feed_field_callback',
        'st_settings_group',
        'st_feed_settings_section'
    );

    add_settings_field(
        'st_feed_urls',
        'RSS Feed URLs',
        'st_feed_urls_field_callback',
        'st_settings_group',
        'st_feed_settings_section'
    );
    add_settings_field(
        'st_rss_feeds_shuffle',
        'RSS Feed URLs shuffle',
        'st_feed_urls_shuffle_field_callback',
        'st_settings_group',
        'st_feed_settings_section'
    );
    add_settings_section(
	    'st_display_options_section', 
	    'Display Options', 
	    'st_display_options_callback', 
	    'st_settings_group'
    );
    add_settings_field(
	    'st_display_position', 
	    'Display Position', 
	    'st_display_position_callback', 
	    'st_settings_group', 
	    'st_display_options_section'
    );
     add_settings_field(
        'st_display_pages',
        'Select Pages to Display Rating',
        'st_display_pages_callback',
        'st_settings_group',
        'st_display_options_section'
    );

}

function st_feed_urls_field_callback() 
{
    $feed_urls = get_option('st_feed_urls', '');
    echo '<textarea id="st_feed_urls" name="st_feed_urls" rows="5" cols="50">' . esc_textarea($feed_urls) . '</textarea>';
    echo '<p>Enter one or more RSS feed URLs, each on a new line.</p>';
}

function st_enable_rss_feed_field_callback() 
{
    $is_enabled = get_option('st_enable_rss_feed', 0);
    echo '<label for="st_enable_rss_feed"><input type="checkbox" name="st_enable_rss_feed" value="1" ' . checked(1, $is_enabled, false) . ' /> Enable RSS Feed Integration</label>';
}
function st_feed_urls_shuffle_field_callback() 
{
    $is_enabled = get_option('st_rss_feeds_shuffle', 0);
    echo '<label for="st_rss_feeds_shuffle"><input type="checkbox" name="st_rss_feeds_shuffle" value="1" ' . checked(1, $is_enabled, false) . ' /> RSS Feeds shuffle</label>';
}
function st_display_options_callback() 
{
    echo '<p>Choose where and how the Rating review should be displayed.</p>';
}
function st_display_position_callback() 
{
    $options = get_option('st_display_position');
    echo "<select name='st_display_position'>
            <option value='bottom_left' " . selected($options, 'bottom_left', false) . ">Bottom left</option>
            <option value='bottom_right' " . selected($options, 'bottom_right', false) . ">Bottom right</option>
            <option value='top_left' " . selected($options, 'top_left', false) . ">Top left</option>
            <option value='top_right' " . selected($options, 'top_right', false) . ">Top right</option>
         </select>";
}
function st_display_pages_callback() 
{
    echo '<p>Select the pages where you want to display the sustainability rating.</p>';
    $pages = get_pages(); // Holen aller Seiten
    $selected_pages = get_option('st_display_pages', []);

    echo '<fieldset>';
    echo '<label>';
    echo '<input type="checkbox" id="all_pages" onclick="toggleAllPages(this.checked);"> ';
    echo 'On All Pages';
    echo '</label>';
    echo '<ul class="flex-container" role="list">';
    foreach ($pages as $page) 
    {
	if(is_array($selected_pages))
	{
          $checked = in_array($page->ID, $selected_pages) ? 'checked="checked"' : '';
	}
	else
	{
	   $checked = '';
	}
        echo '<li>';
        echo '<label>';
        echo '<input type="checkbox" name="st_display_pages[]" value="' . esc_attr($page->ID) . '" ' . $checked . '> ';
        echo esc_html($page->post_title);
        echo '</label>';
        echo '</li>';
    }
    echo '</ul>';
    echo '</fieldset>';
}
add_action('admin_init', 'st_register_settings');

//////////////////////////
function st_register_admin_pages() 
{
    add_menu_page('Sustainability Tracker', 'Sustainability Tracker', 'manage_options', 'sustainability-tracker', 'st_admin_dashboard', 'dashicons-chart-line', 6);
    add_submenu_page('sustainability-tracker', 'Sustainability Tracker Settings', 'Settings', 'manage_options', 'sustainability-tracker-settings', 'st_settings_page');
}
add_action('admin_menu', 'st_register_admin_pages');
////////////////////////////////
function st_settings_page()
{
   ?>
    <div class="wrap">
        <h1>Sustainability Tracker Settings</h1>
        <?php
            if(get_transient('st_notice_error'))
            {
                $messages = get_transient('st_error_messages');
                ?>
                <div class="notice notice-error is-dismissible">
                <?php
                    foreach($messages as $message) { echo '<p>' . esc_html($message) . '</p>'; }
                ?>
                </div>
                <?php
                delete_transient('st_notice_error');
                delete_transient('st_error_messages');
            }
            elseif(get_transient('st_notice_success'))
            {
                $success_messages = get_transient('st_success_messages');
                ?>
                <div class="notice notice-success is-dismissible">
                <?php
                    foreach($success_messages as $s_message) { echo '<p>' . esc_html($s_message ). '</p>'; }
                ?>
                </div>
                <?php
                delete_transient('st_notice_success');
                delete_transient('st_success_messages');
            }
        ?>

        <div class="st-dashboard">
            <div class="st-box">
                <form method="post" action="options.php" enctype="multipart/form-data">
		  <?php wp_nonce_field('st_upload_ga_credentials', 'st_ga_nonce'); ?>
		  <table class="form-table">
		    <tr valign="top">
                      <th scope="row">Tracking Solution</th>
                      <td>
                        <select id="st_tracking_solution" name="st_tracking_solution">
                          <option value="custom" <?php selected(get_option('st_tracking_solution'), 'custom'); ?>>Custom Tracking Solutions</option>
                          <option value="google_analytics" <?php selected(get_option('st_tracking_solution'), 'google_analytics'); ?>>Use Google Analytics API</option>
                        </select>
                      </td>
		      <td>
			<ul>
			  <li><strong>Custom Tracking Solutions:</strong> Select this option to use the built-in tracking functionality provided by this plugin. This option allows you to track page views directly through the plugin without relying on external services.</li>
			  <li><strong>Use Google Analytics API:</strong> Select this option if you want to retrieve page view data from Google Analytics. This does not track page views in real-time but instead queries the Google Analytics API to gather information on page views that have already been recorded in your Google Analytics account.</li>
			</ul>
		      </td>
            	    </tr>
        	  </table>
        <!-- Google Analytics Settings Section -->
	        <div id="google-analytics-settings" style="display: none;">
        	    <h2>Google Analytics Settings</h2>
	            <table class="form-table">
        	        <tr valign="top">
                	    <th scope="row">Google Analytics Credentials JSON Path</th>

	                    <td>
			        <input type="file" id="st_ga_credentials_json" name="st_ga_credentials_json">
			    <?php 
				if (get_option('st_google_credentials_json'))
				{ 
				  echo '<strong><p>Current file: </strong>' . esc_html(basename(get_option('st_google_credentials_json'))) . '</p>'; 
				} 
			    ?>
			  </td>
			  <td>
				<strong><p>You don't know, how to get a Google Analytics Credentials as JSON File?</p></strong><p>You need to create one hear: <a href="https://developers.google.com/analytics/devguides/reporting/data/v1/quickstart-client-libraries?hl=en#step_1_enable_the_api" target="_blank">Enable the API</a></P>
			  </td>
        	        </tr>
                	<tr valign="top">
	                    <th scope="row">Google Analytics Property ID</th>
        	            <td><input type="text" name="st_google_analytics_property_id" value="<?php echo esc_attr(get_option('st_google_analytics_property_id')); ?>" /></td>
		            <td>
				<strong><p>Where cann i find my Google Analytics Property ID?</p></strong>
				<p>Go to Google Analytics and sign in with your Google account.Then choose the account that contains the property you want to find.Click on the Admin gear icon at the bottom left of the screen.Under the <strong>"Property"</strong> column, click on Property Settings.The Property ID is listed at the top of the Property Settings page, under the property name.</p>
			    </td>
                	</tr>
	            </table>
		</div>
 <!-- Jetpack api key -->
                <div id="jetpack-settings" style="display: none;">
                    <h2>Jetpack Settings</h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Jetpack API Key</th>
                            <td><input type="text" name="st_jetpack_api_key" value="<?php echo esc_attr(get_option('st_jetpack_api_key')); ?>" /></td>
                        </tr>
                    </table>
                </div>
                    <?php
                      do_settings_sections('st_settings_group');
		      settings_fields('st_settings_group');
                    ?>
		
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() 
{
    const trackingSolution = document.getElementById('st_tracking_solution');
    const googleAnalyticsSettings = document.getElementById('google-analytics-settings');
    const jetpackSettings = document.getElementById('jetpack-settings');

    function toggleGoogleAnalyticsSettings() 
    {
        if (trackingSolution.value === 'google_analytics') 
        {
            jetpackSettings.style.display = 'none';
            googleAnalyticsSettings.style.display = 'block';
        } 
	else if(trackingSolution.value === 'jetpack')
	{
            googleAnalyticsSettings.style.display = 'none';
            jetpackSettings.style.display = 'block';
        }
	else
	{
            googleAnalyticsSettings.style.display = 'none';
            jetpackSettings.style.display = 'none';
	}
    }

    trackingSolution.addEventListener('change', toggleGoogleAnalyticsSettings);
    toggleGoogleAnalyticsSettings(); // Initial call to set the correct display state
});
</script>


    <?php
}

////////////////////////////////////////////////
function st_custom_mime_types($mimes) 
{
    $mimes['json'] = 'application/json';
    return $mimes;
}
add_filter('upload_mimes', 'st_custom_mime_types');
////////////////////////////////////////////////
function st_sanitize_display_pages($input) 
{
    // Ensure the input is an array of integers (page IDs)
    return array_map('absint', (array)$input);
}

////////////////////////////////////////////////
function st_handle_file_upload() 
{
    if (isset($_POST['st_ga_nonce']) && wp_verify_nonce($_POST['st_ga_nonce'], 'st_upload_ga_credentials')) 
    {
        if (isset($_FILES['st_ga_credentials_json']) && $_FILES['st_ga_credentials_json']['error'] == UPLOAD_ERR_OK) 
	{
            $uploaded_file = $_FILES['st_ga_credentials_json'];

            // Überprüfen, ob die Datei wirklich eine JSON-Datei ist
            $file_type = wp_check_filetype($uploaded_file['name']);
            if ($file_type['ext'] !== 'json') 
	    {
                st_add_admin_message('Please upload a valid JSON file.', 'error');
                return;
            }

            // Erstellen eines temporären Dateipfads für die Seitenladung
            $temp_file = $_FILES['st_ga_credentials_json']['tmp_name'];
            $file = array(
                'name'     => $_FILES['st_ga_credentials_json']['name'],
                'type'     => $_FILES['st_ga_credentials_json']['type'],
                'tmp_name' => $temp_file,
                'error'    => $_FILES['st_ga_credentials_json']['error'],
                'size'     => $_FILES['st_ga_credentials_json']['size']
            );

            $movefile = wp_handle_sideload($file, array('test_form' => false));

            if ($movefile && !isset($movefile['error'])) 
	    {
                update_option('st_google_credentials_json', $movefile['file']);
                st_add_admin_message('File uploaded successfully.', 'success');
            }
	    else 
	    {
                st_add_admin_message('There was an error uploading the file: ' . $movefile['error'], 'error');
            }
        } 
    }
}
add_action('admin_init', 'st_handle_file_upload');



////////////////////////////////////////////////

function st_plugin_option_updated($option_name, $old_value, $value)
{
    // List of options that should trigger the success message
    $tracked_options = [
        'st_tracking_solution',
        'st_google_analytics_property_id',
        'st_ga_credentials_json',
        'st_enable_rss_feed',
        'st_rss_feeds_shuffle',
        'st_feed_urls',
        'st_display_position',
        'st_display_pages'
    ];

    // Only proceed if the option is in the tracked list
    if (in_array($option_name, $tracked_options))
    {
        // Handle both arrays and strings
        $value_changed = false;
        if (is_array($value) && is_array($old_value)) {
            // Sort both arrays to ensure the comparison is order-independent
            sort($value);
            sort($old_value);
            // Compare arrays
            $value_changed = $value !== $old_value;
        } else {
            // Compare scalar values after trimming
            $value_changed = trim((string)$old_value) !== trim((string)$value);
        }

        // Trigger the message if the value has changed
        if ($value_changed) {
            st_add_admin_message('Settings successfully saved', 'success');
        }
    }
}
add_action('update_option', 'st_plugin_option_updated', 10, 3);



////////////////////////////////////////////////
function st_get_custom_page_views($start_date = null, $end_date = null)
{
    global $wpdb;

    // Default to the current month if no date range is provided
    $start_date = $start_date ?: gmdate('Y-m-d', strtotime('-8 years'));
    $end_date = $end_date ?: gmdate('Y-m-t');

    // Generate a unique cache key based on the date range
    $cache_key = "st_custom_page_views_{$start_date}_{$end_date}";
    $cached_result = wp_cache_get($cache_key);

    if ($cached_result !== false) 
    {
        return $cached_result;
    }

    // Prepare the SQL query
    $query = $wpdb->prepare("
        SELECT COUNT(*) AS total_views
        FROM {$wpdb->prefix}st_custom_track_post_views
        WHERE view_date BETWEEN %s AND %s
    ", $start_date, $end_date);

    // Execute the query and get the result
    $total_views = $wpdb->get_var($query);

    // Cache the result
    $total_views = $total_views ? (int)$total_views : 0;
    wp_cache_set($cache_key, $total_views, '', 3600); // Cache for 1 hour

    return $total_views;
}

////////////////////////////////////////
function st_get_page_views_from_jetpack()
{
    if (!class_exists('Jetpack') || !is_plugin_active('jetpack/jetpack.php'))
    {
        st_add_admin_message('Jetpack is selected as the Tracking Solution, but it is not activated or installed.', 'error');
        return 0;
    }
    $site_id = Jetpack::get_option('id');
    $jetpackApiKey= get_option('st_jetpack_api_key', '');
    if (empty($jetpackApiKey) || empty($site_id)) 
    {
        st_add_admin_message('API Schlüssel oder Site ID nicht gesetzt.', 'error');
        return 0;
    } 
    $url = "https://public-api.wordpress.com/rest/v1.1/sites/$site_id/stats";
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $jetpackApiKey
        )
    ));

    if (is_wp_error($response)) 
    {
	st_add_admin_message('Error retrieving statistics from Jetpack!!', 'error');
        return 0;
    }

    $body = wp_remote_retrieve_body($response);
    $statistics=json_decode($body, true);
    if(isset($statistics['error']))
    {
	st_add_admin_message('Jetpack: '.$statistics['message'], 'error');
        return 0;
	
    }
return 0;
    
}
////////////////////////////////////////

function st_get_page_views_from_google_analytics($total)
{
    // Get the credentials and GA4 property ID from the settings
    $credentials_path = get_option('st_google_credentials_json', '');
    $property_id = get_option('st_google_analytics_property_id', '');

    // Check if credentials and property ID are provided
    if (empty($credentials_path) || empty($property_id)) 
    {
        st_add_admin_message('Google Analytics credentials or property ID not provided.', 'error');
        return 0;
    }

    try 
    {
        // Initialize the BetaAnalyticsDataClient for GA4
        $analytics_data_client = new BetaAnalyticsDataClient([
            'credentials' => $credentials_path
        ]);

        // Set up the request
	if($total)
	{
	   $start_date = gmdate('Y-m-d', strtotime('-8 years'));
           $request = (new RunReportRequest())
            	    ->setProperty('properties/' . $property_id)
		     ->setDateRanges([
                        new DateRange([
                            'start_date' => $start_date,
                            'end_date' => 'today',
                        ]),
                    ])

            	    ->setMetrics([
            	        new Metric(['name' => 'screenPageViews'])  // Metric for page views in GA4
            	    ]);
	}
        else
	{
	  $request = (new RunReportRequest())
                    ->setProperty('properties/' . $property_id)
                    ->setDateRanges([
                        new DateRange([
                            'start_date' => gmdate('Y-m').'-01',
                            'end_date' => 'today',
                        ]),
                    ])
                    ->setMetrics([
                        new Metric(['name' => 'screenPageViews'])  // Metric for page views in GA4
                    ]);

	}
        // Run the report and get the data
        $response = $analytics_data_client->runReport($request);

        // Check if the response contains rows
        if ($response->getRows()->count() === 0) 
        {
            st_add_admin_message('No data returned from Google Analytics.', 'warning');
            return 0;
        }

        // Extract the pageviews value
        $page_views = $response->getRows()[0]->getMetricValues()[0]->getValue();

        return (int)$page_views;

    } 
    catch (Exception $e) 
    {
        st_add_admin_message('Error fetching data from Google Analytics: ' . $e->getMessage(), 'error');
        return 0;
    }
}


////////////////////////////////////////
function st_increment_page_views($post_id) 
{
    // Verhindere die Ausführung in der Admin-Oberfläche oder während des Logins
    if (is_admin() || is_login_page()) 
    {
        return;
    }

    // Nur ausführen, wenn es sich um einen einzelnen Beitrag oder eine Seite handelt
    if (!is_single() && !is_page()) 
    {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'st_custom_track_post_views';
    $cache_key = "st_page_views_{$post_id}";
    $cached_views = wp_cache_get($cache_key);

    if ($cached_views === false) 
    {
        $wpdb->insert(
            $table_name,
            [
                'post_id' => $post_id,
                'view_date' => current_time('mysql'),
                'user_id' => get_current_user_id(),
            ],
            [
                '%d',   // post_id as integer
                '%s',   // view_date as string
                '%d'    // user_id as integer
            ]
        );

        $current_views = get_post_meta($post_id, 'st_page_views', true);
        $current_views = $current_views ? (int)$current_views : 0;
        $new_views = $current_views + 1;
        update_post_meta($post_id, 'st_page_views', $new_views);

        wp_cache_set($cache_key, $new_views, '', 3600); // Cache for 1 hour
    }
}

// Füge eine Funktion hinzu, um zu prüfen, ob es sich um die Login-Seite handelt
function is_login_page() 
{
    return in_array($GLOBALS['pagenow'], ['wp-login.php', 'wp-register.php']);
}

add_action('wp_head', function() 
{
    global $post;
    if (isset($post) && $post->ID) {
        st_increment_page_views($post->ID);
    }
});




////////////////////////////////////////////////
function st_carbon_footprint_ajax() 
{
    // Überprüfe den Nonce
    if (!isset($_POST['st_nonce']) || !wp_verify_nonce($_POST['st_nonce'], 'st_calculate_carbon_footprint_nonce')) 
    {
        wp_send_json_error('Invalid Nonce');
        wp_die(); // Stoppe die Ausführung für Sicherheit
    }

    // Verarbeite die Anfrage nur, wenn der Nonce gültig ist
    $page_views = intval($_POST['page_views']);
    $carbon_footprint = st_calculate_carbon_footprint($page_views);
    $carbon_offset = st_get_carbon_offset($carbon_footprint);

    wp_send_json_success(array(
        'carbon_footprint' => $carbon_footprint,
        'carbon_offset' => $carbon_offset
    ));
}

add_action('wp_ajax_st_carbon_footprint', 'st_carbon_footprint_ajax');

///////////////////////////////////////////////
function fetch_rss_feed($feed_url) 

{
    $response = wp_remote_get($feed_url);

    if (is_wp_error($response)) 
    {
        return [];
    }

    $rss = simplexml_load_string(wp_remote_retrieve_body($response));

    if (!$rss) 
    {
        return [];
    }

    $items = [];
    foreach ($rss->channel->item as $item) 
    {
        $items[] = 
        [
            'title' => (string) $item->title,
            'link' => (string) $item->link,
            'description' => (string) $item->description,
            'pubDate' => (string) $item->pubDate
        ];
    }

    return $items;
}
//////////////////////////////////////////////
function st_display_sustainability_rating($content)
{
  if (is_page()) 
  {
   global $post;
   $selected_pages = get_option('st_display_pages', []);

   if (in_array($post->ID, $selected_pages)) 
   {
       // Assume $rating_overview is accessible in this scope
       $tracking_solution = get_option('st_tracking_solution');
       $position = get_option('st_display_position', 'bottom_right');
       switch ($tracking_solution) 
       {
           case 'google_analytics':
               $total_page_views = st_get_page_views_from_google_analytics(true);
               break;
           case 'custom':
           default:
               $total_page_views = st_get_custom_page_views(true);
               break;
       }

       $total_carbon_footprint = st_calculate_carbon_footprint($total_page_views);
       $total_carbon_offset = st_get_carbon_offset($total_carbon_footprint);

       $rating = rating_function($total_carbon_footprint, $total_carbon_offset);
       $globalRating=[
	       		'A+' => '50% Lower than Global Average | 🌱 Eco-Friendly',
			'A'  => '30% Lower than Global Average | 🌱 Eco-Friendly',
			'B'  => '20% Lower than Global Average | 🌱 Eco-Friendly',
			'C'  => '10% Lower than Global Average | 🌱 Eco-Friendly',
			'D'  => 'in the Global Average',
			'E'  => '20% More than Global Average',
       		     ];
       // Append the rating overview to the content
       $content .= "<div class='sustainability-rating {$position} {$rating}'>Rating: {$rating} | {$globalRating[$rating]} </div>";
   }
    }

    return $content;
}
add_filter('the_content', 'st_display_sustainability_rating');
function st_enqueue_styles() 
{
    wp_enqueue_style('st-styles', ST_PLUGIN_URL . 'assets/css/rating-style.css');
}
add_action('wp_enqueue_scripts', 'st_enqueue_styles');


