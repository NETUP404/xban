<?php

// Mostrar el formulario de envío de banner
function bes_display_submit_banner_form() {
    if ( ! is_user_logged_in() ) {
        return;
    }

    ob_start();
    ?>
    <style>
        .bes-submit-banner-form th, .bes-submit-banner-form td {
            padding: 10px;
            text-align: left;
            border: none;
        }
        .bes-submit-banner-form input[type="text"],
        .bes-submit-banner-form input[type="url"] {
            width: 100%;
            padding: 10px;
            margin: 0;
        }
        .bes-submit-banner-form input[type="submit"] {
            background: #0073aa;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        .bes-submit-banner-form input[type="submit"]:hover {
            background: #005177;
        }
        .bes-warning {
            background: #fbf9d8;
            border: 1px solid #817900;
            color: #856404;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            font-family: 'Arial', sans-serif;
            align-items: center;
        }
        .bes-warning-icon {
            margin-right: 10px;
            font-size: 20px;
        }
    </style>
    <div class="bes-submit-banner-form">
        <div class="bes-warning">
            <p>Al enviar tu banner, aceptas que nuestro equipo revisará tanto el dominio como el diseño. Solo se aceptarán banners profesionales, sin elementos agresivos, intrusivos o de baja calidad.</p>
        </div>
        <form id="bes-banner-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('bes_submit_banner_nonce', 'bes_nonce'); ?>
            <table>
                <tbody>
                    <tr>
                        <td><input type="text" name="banner_url" id="banner_url" placeholder="URL de Imagen" required></td>
                        <td><input type="url" name="target_url" id="target_url" placeholder="URL de Campaña" required></td>
                        <td><input type="submit" name="bes_submit" value="Enviar Banner"></td>
                    </tr>
                </tbody>
            </table>
        </form>
        <div id="bes-form-response"></div>
    </div>
    <?php
    return ob_get_clean();
}

// Manejar la presentación del formulario de banner
function bes_handle_banner_submission() {
    if ( ! is_user_logged_in() ) {
        wp_send_json_error(['message' => 'Usuario no autenticado.']);
        return;
    }
    
    check_ajax_referer('bes_submit_banner_nonce', 'bes_nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'bes_banners';
    
    $user_id = get_current_user_id();
    $banner_url = sanitize_text_field( $_POST['banner_url'] );
    $target_url = esc_url( $_POST['target_url'] );

    // Check if the banner already exists
    $existing_banner = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND banner_url = %s",
            $user_id, $banner_url
        )
    );

    if ($existing_banner > 0) {
        wp_send_json_error(['message' => 'Duplicate banner submission detected. Please check your banners.']);
        return;
    }

    $wpdb->insert(
        $table_name,
        [
            'user_id' => $user_id,
            'banner_url' => $banner_url,
            'target_url' => $target_url,
            'impressions' => 0,
            'clicks' => 0,
            'credits' => 0,
            'approved' => 0
        ]
    );

    wp_send_json_success(['message' => 'Banner enviado correctamente. Espera la aprobación del administrador.']);
}

// Registro de la acción AJAX para manejar el envío del formulario
add_action('wp_ajax_bes_handle_banner_submission', 'bes_handle_banner_submission');
add_action('wp_ajax_nopriv_bes_handle_banner_submission', 'bes_handle_banner_submission');

// Incluir el script de clic de banner
function bes_track_click_script() {
    ?>
    <script type="text/javascript">
        function bes_track_click(banner_id) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "<?php echo admin_url('admin-ajax.php'); ?>", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.send("action=bes_track_click&banner_id=" + banner_id);
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('bes-banner-form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
                
                xhr.onload = function() {
                    var response = JSON.parse(this.responseText);
                    var responseDiv = document.getElementById('bes-form-response');
                    responseDiv.innerHTML = response.data.message;
                };
                
                formData.append('action', 'bes_handle_banner_submission');
                xhr.send(formData);
            });
        });
    </script>
    <?php
}
add_action('wp_footer', 'bes_track_click_script');

// Manejar la solicitud AJAX de clic de banner
function bes_track_click_ajax() {
    if (isset($_POST['banner_id'])) {
        bes_track_click(intval($_POST['banner_id']));
    }
    wp_die();
}
add_action('wp_ajax_bes_track_click', 'bes_track_click_ajax');
add_action('wp_ajax_nopriv_bes_track_click', 'bes_track_click_ajax');

// Registrar el shortcode para mostrar el banner
function bes_register_banner_display_shortcode() {
    add_shortcode('bes_display_banner', 'bes_display_banner_shortcode');
}

// Función del shortcode para mostrar el banner
function bes_display_banner_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => 0,
    ), $atts, 'bes_display_banner');

    ob_start();
    bes_display_banner($atts['id']);
    return ob_get_clean();
}

add_action('init', 'bes_register_banner_display_shortcode');