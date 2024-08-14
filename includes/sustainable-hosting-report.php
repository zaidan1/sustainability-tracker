<?php
function st_sustainable_hosting_report($total_carbon_footprint,$carbon_offset,$total_page_views) 
{
    ob_start();
    $domain=$_SERVER['HTTP_HOST'];
    $ip = $_SERVER['SERVER_ADDR'];
    $renewable_energy_image='<img src="https://app.greenweb.org/api/v3/greencheckimage/'.$domain.'?nocache=true" alt="This website runs on green hosting - verified by thegreenwebfoundation.org" width="200px" height="95px">';
    $url = "https://api.thegreenwebfoundation.org/greencheck/{$domain}";
    $response = wp_remote_get($url);
    if (is_wp_error($response))
    {
        $renewable_energy_usage= 'Unknown';
	$provider='Unknown';
    }
    else
    {
      $body = wp_remote_retrieve_body($response);
      $data = json_decode($body, true);
      $renewable_energy_usage=isset($data['green']) && $data['green'] ? 'Yes' : 'No';
      $provider=$data['hosted_by'];
    }

    ?>
    
    <div class="st-box">
        <h2>Sustainable Hosting Report</h2>
        <p><strong>Hosting Provider:</strong> <?php echo $provider; ?></p>
        <p><strong>Renewable Energy Usage:</strong></p>
        <p><?php echo $renewable_energy_image; ?></p>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Renewable Energy Usage</th>
                <td><p><?php echo esc_attr($renewable_energy_usage); ?></p></td>
            </tr>
            <tr valign="top">
                <th scope="row">Total Page views</th>
                <td><p><?php echo number_format($total_page_views); ?> </p></td>
            </tr>
            <tr valign="top">
                <th scope="row">Total Carbon Footprint</th>
                <td><p><?php echo number_format($total_carbon_footprint,2); ?> KG CO₂</p></td>
            </tr>
            <tr valign="top">
                <th scope="row">Carbon Offset cost</th>
                <td>
                    <p><?php echo number_format($carbon_offset,2); ?> $</p>
                </td>
            </tr>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('st_hosting_report', 'st_sustainable_hosting_report');
//////////////////////////


