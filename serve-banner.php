<?php
if (!isset($_GET['user_id'])) {
    exit('No user ID provided.');
}

$user_id = intval($_GET['user_id']);

require_once('../../../wp-load.php'); // Cargar WordPress

global $wpdb;
$table_name = $wpdb->prefix . 'bes_banners';

// Seleccionar un banner aleatorio aprobado que no pertenezca al usuario actual
$banner = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id != %d AND approved = 1 ORDER BY RAND() LIMIT 1", $user_id));

if (!$banner) {
    exit('No approved banners found from other users.');
}

$ip = $_SERVER['REMOTE_ADDR'];
$impression_cookie = "bes_impression_{$banner->id}";
$click_cookie = "bes_click_{$banner->id}";

if (!isset($_COOKIE[$impression_cookie])) {
    setcookie($impression_cookie, $ip, time() + 3600 * 24, "/");
    $wpdb->query($wpdb->prepare("UPDATE $table_name SET impressions = impressions + 1 WHERE id = %d", $banner->id));
}

if (isset($_GET['click'])) {
    if (!isset($_COOKIE[$click_cookie])) {
        setcookie($click_cookie, $ip, time() + 3600 * 24, "/");
        $wpdb->query($wpdb->prepare("UPDATE $table_name SET clicks = clicks + 1, credits = credits + 20 WHERE id = %d", $banner->id));
    }
    header("Location: " . esc_url($banner->target_url));
    exit;
}

header("Content-Type: application/javascript");
?>
document.write(`
    <style>
        .banner-container {
            position: relative;
            width: 721px;
            height: 91px;
            overflow: hidden;
            border: 0px solid #ff8b00;
        }
        .banner-icon {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 20px;
            height: 20px;
            transition: opacity 0.2s;
        }
        .banner-icon:hover {
            opacity: 0.6;
        }
        .banner-image {
            width: 100%;
            height: 100%;
        }
    </style>
    <div class="banner-container">
        <a href="https://www.webanner.net" target="_blank">
            <img src="https://webanner.net/wp-content/uploads/2025/01/icobanner.png" class="banner-icon" alt="Icon">
        </a>
        <a href="<?php echo esc_url(add_query_arg(array('user_id' => $user_id, 'click' => 1), site_url('/wp-content/plugins/banner-exchange/serve-banner.php'))); ?>" target="_blank">
            <img src="<?php echo esc_url($banner->banner_url); ?>" alt="Banner" class="banner-image">
        </a>
    </div>
`);