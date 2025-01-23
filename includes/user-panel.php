<?php

// Mostrar las estadísticas de usuario
function bes_display_user_statistics() {
    if (!is_user_logged_in()) {
        return '<p>Debes estar logueado para ver esta página.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'bes_banners';
    $user_id = get_current_user_id();

    $banners = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE user_id = %d",
        $user_id
    ));

    // Calcular totales de clics, impresiones y créditos
    $total_clicks = 0;
    $total_impressions = 0;
    $total_credits = 0;

    foreach ($banners as $banner) {
        $total_clicks += $banner->clicks;
        $total_impressions += $banner->impressions;
        $total_credits += $banner->credits;
    }

    ob_start();
    ?>
    <div class="bes-user-panel">
        <div class="bes-info-box">
            <p>En nuestro sistema, cada impresión de tu banner te otorga 1 crédito, mientras que cada clic genera 20 créditos. Estos créditos se convierten automáticamente en más impresiones para tu campaña.</p>
            <p><strong>Total de Impresiones:</strong> <?php echo $total_impressions; ?></p>
            <p><strong>Total de Clics:</strong> <?php echo $total_clicks; ?></p>
            <p><strong>Créditos Restantes:</strong> <?php echo $total_credits; ?></p>
            <div class="bes-copy-container">
                <textarea readonly class="bes-copy-textarea" id="banner-code"><?php echo '<script src="https://webanner.net/wp-content/plugins/banner-exchange/serve-banner.php?user_id=' . $user_id . '"></script>'; ?></textarea>
                <button class="bes-copy-button" onclick="copyToClipboard('banner-code')">Copiar</button>
            </div>
        </div>
        <div class="bes-card-container">
            <?php foreach ($banners as $index => $banner): ?>
                <?php if ($index % 2 == 0): ?>
                    <div class="bes-card-row">
                <?php endif; ?>
                
                <div class="bes-card">
                    <div class="bes-card-header">
                        <h3>ID CAMPAÑA: <span class="banner-id"><?php echo $banner->id; ?></span></h3>
                        <img src="<?php echo esc_url($banner->banner_url); ?>" class="banner-image" alt="Banner">
                    </div>
                    <div class="bes-card-body">
                        <p><strong>URL del Banner:</strong> <a href="<?php echo esc_url($banner->banner_url); ?>" target="_blank"><?php echo esc_url($banner->banner_url); ?></a></p>
                        <hr class="bes-divider">
                        <p><strong>URL de la Campaña:</strong> <a href="<?php echo esc_url($banner->target_url); ?>" target="_blank"><?php echo esc_url($banner->target_url); ?></a></p>
                        <hr class="bes-divider">
                        <p><strong>Impresiones Recibidas:</strong> <?php echo $banner->impressions; ?></p>
                        <hr class="bes-divider">
                        <p><strong>Clicks Recibidos:</strong> <?php echo $banner->clicks; ?></p>
                        <hr class="bes-divider">
                        <p><strong>Créditos Disponibles:</strong> <?php echo $banner->credits; ?></p>
                        <hr class="bes-divider">
                        <p><strong>Aprobado:</strong> <?php echo $banner->approved ? 'Sí' : 'No'; ?></p>
                    </div>
                        <div class="bes-card-footer">
                          <form method="post" class="delete-form" data-banner-id="<?php echo esc_attr($banner->id); ?>">
                           <?php wp_nonce_field('delete_banner_nonce', 'delete_nonce'); ?>
                            <input type="hidden" name="banner_id" value="<?php echo esc_attr($banner->id); ?>">
                            <input type="button" name="delete_banner" value="Borrar" class="button button-danger delete-banner-button">
                           </form>
                       </div>
                </div>

                <?php if ($index % 2 == 1 || $index == count($banners) - 1): ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <style>
        body {
            background-color: #ffffff; /* Fondo blanco */
        }
        .bes-user-panel {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0px;
            border-radius: 8px;
        }
        .bes-user-panel h2 {
            margin-bottom: 20px;
            color: #003366;
            text-align: center;
            font-family: 'Arial', sans-serif;
        }
        .bes-info-box {
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #003366;
            background-color: #e6f2ff;
            border-radius: 8px;
            font-family: 'Arial', sans-serif;
        }
        .bes-card-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .bes-card-row {
            display: flex;
            gap: 20px;
        }
        .bes-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: calc(50% - 10px);
            overflow: hidden;
        }
        .bes-card-header {
            text-align: center;
            background-color: #003366;
            padding: 10px;
        }
        .bes-card-header h3 {
            margin: 0;
            font-family: 'Arial', sans-serif;
            color: white; /* Texto en blanco */
        }
        .banner-id {
            color: white; /* Texto en blanco */
        }
        .banner-image {
            max-width: 100%;
            max-height: 150px;
            margin-top: 10px;
            border-radius: 4px;
        }
        .bes-card-body {
            padding: 15px;
            font-family: 'Arial', sans-serif;
        }
        .bes-card-body p {
            margin: 10px 0;
        }
        .bes-card-body a {
            color: #003366;
            text-decoration: none;
        }
        .bes-card-body a:hover {
            text-decoration: underline;
        }
        .bes-copy-container {
            position: relative;
            margin-top: 20px;
        }
        .bes-copy-textarea {
            width: 100%;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            font-family: 'Arial', sans-serif;
        }
        .bes-copy-button {
            position: absolute;
            right: 10px;
            top: 10px;
            background-color: #0073aa;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Arial', sans-serif;
        }
        .bes-copy-button:hover {
            background-color: #005177;
        }
        .bes-card-footer {
            padding: 15px;
            text-align: center;
        }
        .button-danger {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Arial', sans-serif;
        }
        .button-danger:hover {
            background-color: #c9302c;
        }
        .delete-form {
            display: inline-block;
        }
        .bes-divider {
            border: none;
            border-top: 1px solid #ddd;
            margin: 10px 0;
        }
    </style>
    <script>
        function copyToClipboard(elementId) {
            var copyText = document.getElementById(elementId);
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");

            alert("Código copiado: " + copyText.value);
        }

        jQuery(document).ready(function($) {
            $('.delete-banner-button').on('click', function(e) {
                e.preventDefault();

                if (!confirm('Are you sure you want to delete this banner?')) {
                    return;
                }

                var bannerId = $(this).closest('.delete-form').data('banner-id');
                var nonce = $(this).closest('.delete-form').find('input[name="delete_nonce"]').val();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'delete_banner',
                        banner_id: bannerId,
                        _ajax_nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Banner deleted successfully.');
                            location.reload();
                        } else {
                            alert('Failed to delete banner: ' + response.data);
                        }
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// Registrar el shortcode para mostrar las estadísticas de usuario
function bes_register_user_panel_shortcode() {
    add_shortcode('bes_user_statistics', 'bes_display_user_statistics');
}
add_action('init', 'bes_register_user_panel_shortcode');

// Manejar la solicitud de eliminación de banner
function bes_handle_delete_banner_request() {
    if (is_user_logged_in() && isset($_POST['banner_id']) && check_ajax_referer('delete_banner_nonce', '_ajax_nonce', false)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bes_banners';
        $banner_id = intval($_POST['banner_id']);
        $user_id = get_current_user_id();

        // Verificar si el banner pertenece al usuario actual
        $banner = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d AND user_id = %d", $banner_id, $user_id));

        if ($banner) {
            $wpdb->delete($table_name, ['id' => $banner_id]);
            wp_send_json_success('Banner deleted');
        } else {
            wp_send_json_error('Banner not found');
        }
    } else {
        wp_send_json_error('Invalid request');
    }
}
add_action('wp_ajax_delete_banner', 'bes_handle_delete_banner_request');