<?php
/*
Plugin Name: BMI Calculator
Description: A simple plugin to calculate BMI and provide recommendations.
Version: 0.01
Author: Rick Hayes
License: MIT
License URI: https://opensource.org/licenses/MIT

Version History:
- 0.01 (2025-03-29): Initial release with basic BMI calculation and recommendations.
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue CSS for styling
function bmi_calculator_enqueue_styles() {
    wp_enqueue_style('bmi-calculator-style', plugins_url('css/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'bmi_calculator_enqueue_styles');

// Shortcode function to display the BMI calculator
function bmi_calculator_shortcode() {
    ob_start(); // Start output buffering

    // Process form submission
    if (isset($_POST['bmi_submit'])) {
        $height_unit = sanitize_text_field($_POST['height_unit']);
        $weight_unit = sanitize_text_field($_POST['weight_unit']);
        $height = floatval($_POST['height']);
        $weight = floatval($_POST['weight']);
        $height_inches = isset($_POST['height_inches']) ? floatval($_POST['height_inches']) : 0;

        // Calculate BMI
        if ($height > 0 && $weight > 0) {
            if ($height_unit == 'feet' && $weight_unit == 'lbs') {
                $total_height_inches = ($height * 12) + $height_inches;
                $bmi = ($weight * 703) / ($total_height_inches * $total_height_inches);
            } else {
                $bmi = $weight / ($height * $height);
            }
            $bmi = round($bmi, 1);

            // Determine recommendation
            $recommendation = '';
            if ($bmi < 18.5) {
                $recommendation = "Underweight: Consider consulting a nutritionist to ensure you're getting enough calories and nutrients. Try some hearty recipes!";
            } elseif ($bmi >= 18.5 && $bmi < 25) {
                $recommendation = "Normal weight: Great job maintaining a healthy range! Explore our recipes to keep things balanced.";
            } elseif ($bmi >= 25 && $bmi < 30) {
                $recommendation = "Overweight: Small changes can help! Check out our lighter recipes for inspiration.";
            } else {
                $recommendation = "Obese: It might be time to talk to a healthcare provider. We’ve got some healthy recipes to start.";
            }
        }
    }
    ?>

    <div class="bmi-calculator">
        <h2>Calculate Your BMI</h2>
        <form method="post" action="">
            <div class="form-group">
                <label for="height">Height:</label>
                <input type="number" step="0.01" name="height" id="height" min="0" required placeholder="e.g., 5 or 1.75">
                <select name="height_unit" id="height_unit">
                    <option value="meters">Meters</option>
                    <option value="feet">Feet</option>
                </select>
                <input type="number" name="height_inches" id="height_inches" min="0" max="11" placeholder="Inches (if feet)" style="display:none;">
            </div>
            <div class="form-group">
                <label for="weight">Weight:</label>
                <input type="number" step="0.01" name="weight" id="weight" min="0" required placeholder="e.g., 150 or 68">
                <select name="weight_unit" id="weight_unit">
                    <option value="kg">Kilograms</option>
                    <option value="lbs">Pounds</option>
                </select>
            </div>
            <input type="submit" name="bmi_submit" value="Calculate BMI" class="submit-btn">
        </form>

        <?php if (isset($bmi)) { ?>
            <div class="bmi-result">
                <h3>Your BMI: <?php echo $bmi; ?></h3>
                <p><?php echo $recommendation; ?></p>
            </div>
        <?php } ?>
    </div>

    <script>
        // Show/hide inches field based on height unit selection
        document.getElementById('height_unit').addEventListener('change', function() {
            var inchesField = document.getElementById('height_inches');
            inchesField.style.display = (this.value === 'feet') ? 'inline-block' : 'none';
            inchesField.required = (this.value === 'feet');
        });
    </script>

    <?php
    return ob_get_clean(); // Return the buffered output
}
add_shortcode('bmi_calculator', 'bmi_calculator_shortcode');

// Register and create CSS file on activation
function bmi_calculator_activate() {
    $css_dir = plugin_dir_path(__FILE__) . 'css/';
    if (!file_exists($css_dir)) {
        mkdir($css_dir, 0755, true);
    }
    $css_file = $css_dir . 'style.css';
    if (!file_exists($css_file)) {
        $css_content = "
            .bmi-calculator { max-width: 500px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
            .form-group { margin-bottom: 15px; }
            label { display: inline-block; width: 80px; }
            input[type='number'] { width: 100px; padding: 5px; }
            select { padding: 5px; }
            .submit-btn { background-color: #0073aa; color: white; padding: 10px 20px; border: none; cursor: pointer; }
            .submit-btn:hover { background-color: #005d82; }
            .bmi-result { margin-top: 20px; padding: 10px; background-color: #f5f5f5; border-radius: 5px; }
            .bmi-result h3 { margin: 0 0 10px; }
        ";
        file_put_contents($css_file, $css_content);
    }
}
register_activation_hook(__FILE__, 'bmi_calculator_activate');
