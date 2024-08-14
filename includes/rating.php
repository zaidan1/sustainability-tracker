<?php
function rating($page_views,$carbon_footprint,$carbon_offset,$rating) 
{
    ob_start();
    $domain=$_SERVER['HTTP_HOST'];
    $ip = $_SERVER['SERVER_ADDR'];
    ?>
     <div class="st-dashboard">
            <!-- Overview Section -->
            <div class="st-box">
                <h2>Overview</h2>
                <p><strong>Monthly Page Views:</strong> <?php echo $page_views; ?></p>
                <p><strong>Total Carbon Footprint:</strong> <?php echo number_format($carbon_footprint,2); ?> kg  CO₂</p>
                <p><strong>Carbon Offset:</strong> <?php echo number_format($carbon_offset,2); ?> $<sub style="color: gray;"> $10 per ton of CO2 offset (1000 kg)</sub></p>
                <p><strong>Rating:</strong></p>
                <div class="rating-system">
                    <div class="rating-header">
                        <h2>Hurrah! This web page achieves a carbon rating of <span id="carbon-rating"><?php echo $rating; ?></span></h2>
                    </div>
                    <div class="rating-bar">
                        <div class="rating-levels">
                            <div class="level A-plus">A+</div>
                            <div class="level A"><strong>A</strong></div>
                            <div class="level B"><strong>B</strong></div>
                            <div class="level C"><strong>C</strong></div>
                            <div class="level D"><strong>D</strong></div>
                            <div class="level E"><strong>E</strong></div>
                            <div class="level F"><strong>F</strong></div>
                        </div>
                        <div class="rating-indicator" id="rating-indicator"><span class="dashicons dashicons-arrow-up-alt2"></span></div>
                    </div>
                </div>
            </div>
<?php
    return ob_get_clean();
}
add_shortcode('rating_overview', 'rating');

