<?php
function rating_function($carbon_footprint,$carbon_offset) 
{
    ob_start();
    $domain=$_SERVER['HTTP_HOST'];
    $ip = $_SERVER['SERVER_ADDR'];
     $url = "https://api.thegreenwebfoundation.org/greencheck/{$domain}";
    $response = wp_remote_get($url);
    if (is_wp_error($response))
    {
        return 'Unknown';
    }
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    $is_renewable_energy=isset($data['green']) && $data['green'] ? 'Yes' : 'No';
    $score = 100 - ($carbon_footprint - $carbon_offset);
    if ($is_renewable_energy === 'Yes')
    {
        $score += 10;
    }
    $rating_score = $score > 100 ? 'A+' : ($score > 90 ? 'A' : ($score > 80 ? 'B' : ($score > 70 ? 'C' : ($score > 60 ? 'D' : 'E'))));
   return $rating_score;
}
add_shortcode('rating_function', 'rating_function');

