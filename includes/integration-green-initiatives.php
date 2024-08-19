<?php
function st_integration_green_initiatives() 
{
    ob_start();
    $is_enabled = get_option('st_enable_rss_feed', 0);
    $feed_urls = get_option('st_feed_urls', '');
    if (!$is_enabled || empty($feed_urls))
    {
        $warn ='RSS Feed integration is disabled or no RSS feed URLs provided.';
    }
    else
    {
       $urls = array_filter(array_map('trim', explode("\n", $feed_urls)));
       $items = [];

       foreach ($urls as $url)
       {
           $feed_items = fetch_rss_feed($url);
           if ($feed_items)
           {
               $items = array_merge($items, $feed_items);
           }
       }

       if (empty($items))
       {
           $warn ='No items found from the provided RSS feeds';
       }
       // Check if shuffle is enabled
       $shuffle = get_option('st_rss_feeds_shuffle', 0);
       if ($shuffle)
       {
           shuffle($items);
       }
    }
    if(!isset($warn) || !$warn)
    {
      echo '<div class="st-box">
            <h2>Green Initiatives Feeds</h2>
	    <div class="rss-slider">
		<P>';
       $item_count=1;
       foreach ($items as $item)
       {
         // Force URL to begin with "http://" or "https://" so 'parse_url' works
           $url = preg_replace('/^(?!https?:\/\/)(.*:\/\/)/i', 'http://', $item['link']);
           $parts = wp_parse_url($url);
           $host = isset($parts['host']) ? $parts['host'] : 'Unknown source in the Item';
           echo '<div class="rss-slide">';
           echo esc_html($item_count).'/'.count($items).' - '.esc_html($host);
           echo '<h3><a href="' . esc_url($item['link']) . '" target="_blank">' . esc_html($item['title']) . '</a></h3>';
           echo '<p>' . $item['description'] . '</p><HR>';
           echo '<span class="rss-date">' . esc_html($item['pubDate']) . '</span><HR>';
           echo '<span class="rss-date">Source: ' . esc_html($host) . '</span><HR>';
           echo '</div>';
           $item_count++;
       }
       echo '</div>';
        // Add navigation buttons
       echo '<div class="rss-slider-nav">';
       echo '<button id="prev-slide"><span class="dashicons dashicons-arrow-left-alt2"></span></button>';
       echo '<button id="next-slide"><span class="dashicons dashicons-arrow-right-alt2"></span></button>';
       echo '</p>
           </div>
         </div>';
       ?>
        <script>
         document.addEventListener('DOMContentLoaded', function()
         {
            const slides = document.querySelectorAll('.rss-slide');
            let currentSlide = 0;
            let slideInterval = setInterval(nextSlide, 5000); // Automatically change slide every 5 seconds

            function showSlide(index)
            {
                slides.forEach((slide, i) =>
                {
                    slide.style.display = i === index ? 'block' : 'none';
                });
            }

            function nextSlide()
            {
                currentSlide = (currentSlide + 1) % slides.length;
                showSlide(currentSlide);
            }

            function prevSlide()
            {
                currentSlide = (currentSlide - 1 + slides.length) % slides.length;
                showSlide(currentSlide);
            }

            function stopAutoSlide()
            {
                clearInterval(slideInterval); // Stops the automatic sliding
            }

            document.getElementById('next-slide').addEventListener('click', function()
            {
                nextSlide();
                stopAutoSlide(); // Stop the auto-slide when the next button is clicked
            });

            document.getElementById('prev-slide').addEventListener('click', function()
            {
                prevSlide();
                stopAutoSlide(); // Stop the auto-slide when the previous button is clicked
            });

            // Show the first slide initially
            showSlide(currentSlide);
       });
       </script>
       <?php
     }
     else
     {
       echo '<div class="st-box">
              <h2>Green Initiatives Feeds</h2>
	      <div class="rss-slider">
		<P>'.esc_html($warn).'</p>
	      </div>
	    </div>';
     }
    return ob_get_clean();
}
add_shortcode('st_green_initiatives', 'st_integration_green_initiatives');

