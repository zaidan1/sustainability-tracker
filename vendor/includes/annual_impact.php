<?php
function st_annual_impact_initiatives($page_views,$carbon_footprint) 
{
    ob_start();
    $annual_page_views = $page_views * 12;
    $annual_carbon_footprint = $carbon_footprint * 12; // Beispielwerte, bitte anpassen
    $kwh_energy = $annual_carbon_footprint / 0.233; // Umrechnung von kg CO₂ in kWh (Beispielwert)
    $cups_of_tea = $annual_carbon_footprint * 135;
    $smartphone_charges = $annual_carbon_footprint * 188;
    $trees_needed = $annual_carbon_footprint / 21; // Annahme: 1 Baum absorbiert 21 kg CO₂ pro Jahr
    $electric_car_distance = $kwh_energy * 5; //  1 kWh reicht für 5 km
?>
      <div class="st-box">
                <h2 class="hndle"><span>Annual Impact</span></h2>
                <div class="inside">
                    <p>Over a year, <strong style="color:red;"><?php echo number_format($annual_page_views); ?></strong> is the avarage page views, produces <strong style="color:red;"><?php echo number_format($annual_carbon_footprint,2,','); ?></strong> kg of  CO₂ equivalent.</p>
                    <p>As much  CO₂ as boiling water for<strong style="color:red;"> <?php echo number_format($cups_of_tea); ?></strong> cups of tea.<sub style="color:grey;"> (Number of cups of tea that could be made with the corresponding energy, based on the average CO₂ emissions per cup.)</sub><p>
                    <p> As much  CO₂ as <strong style="color:red;"><?php echo number_format($smartphone_charges); ?></strong> full charges of an average smartphone.<sub style="color:grey;"> (Based on the average  CO₂ emissions per charge.)</sub></p>
                    <p>This web page emits the amount of carbon that <strong style="color:red;"><?php echo number_format($trees_needed); ?></strong> trees absorb in a year.<sub style="color:grey;"> (Annahme: 1 Baum absorbiert 21 kg  CO₂ pro Jahr)</sub></p>
                    <p><strong style="color:red;"><?php echo number_format($kwh_energy,2,','); ?></strong> kWh of energy,that’s enough electricity to drive an electric car <strong style="color:red;"><?php echo number_format($electric_car_distance,2,','); ?> </strong>km.<sub style="color:grey;"> (1 kWh reicht für 5 km)</sub></p>
                </div>
            </div>
<?php
    return ob_get_clean();
}
add_shortcode('st_annual_impact', 'st_annual_Impact_initiatives');

