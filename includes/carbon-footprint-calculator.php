<?php
function st_carbon_footprint_calculator($page_views) 
{
    ob_start();
    ?>
     <div class="st-box">
                <h2>Carbon Footprint Calculator</h2>
                <p for="page-views">Monthly Page Views: <strong><?php echo esc_html($page_views); ?></strong></p>
                <form id="st-carbon-footprint-form" method="post" action="">
                    <table>
                      <tr>
                        <td>
                          <input type="number" id="page-views" name="page_views" value="<?php echo esc_html($page_views); ?>" required>
                        </td>
                        <td>
                            <button type="submit" class="button button-primary">Calculate</button>
                        </td>
                      </tr>
                    </table>
                </form>
                <div id="st-carbon-footprint-result"></div>
                <canvas id="carbonFootprintChart" width="auto" height="auto"></canvas>
            </div>

    <?php
    return ob_get_clean();
}
add_shortcode('st_carbon_calculator', 'st_carbon_footprint_calculator');

