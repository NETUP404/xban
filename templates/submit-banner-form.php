<?php
if ( isset( $_POST['bes_submit'] ) ) {
    bes_handle_banner_submission();
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

global $wpdb;
$table_name = $wpdb->prefix . 'bes_banners';
$user_credits = $wpdb->get_var($wpdb->prepare(
    "SELECT SUM(credits) FROM $table_name WHERE user_id = %d",
    $user_id
));

$user_script = bes_generate_user_script($user_id);
?>

<div class="bes-submit-banner-form-horizontal">
    <form method="post" enctype="multipart/form-data">
        <?php wp_nonce_field('bes_submit_banner_nonce', 'bes_nonce'); ?>
        <div class="form-group-horizontal">
            <label for="banner_url">URL del Banner:</label>
            <input type="text" name="banner_url" required>
        </div>

        <div class="form-group-horizontal">
            <label for="target_url">URL de Destino:</label>
            <input type="url" name="target_url" required>
        </div>

        <div class="form-group-horizontal">
            <label for="title">Título de Campaña:</label>
            <input type="text" name="title" id="title" placeholder="Título de Campaña (máx 62 caracteres)" maxlength="62" required>
        </div>

        <div class="form-group-horizontal">
            <input type="submit" name="bes_submit" value="Enviar Banner" class="button-horizontal">
        </div>
    </form>
</div>

<div class="bes-credits-box">
    <h3>Créditos Disponibles</h3>
    <div class="credits-value"><?php echo $user_credits; ?></div>
</div>

<div class="bes-user-script-box">
    <h3>Código para Insertar en tu Web</h3>
    <textarea readonly><?php echo htmlspecialchars($user_script); ?></textarea>
</div>

<style>
    .bes-submit-banner-form-horizontal {
        margin-bottom: 20px;
    }
    .form-group-horizontal {
        margin-bottom: 15px;
    }
    .form-group-horizontal label {
        display: block;
        margin-bottom: 5px;
    }
    .form-group-horizontal input[type="text"],
    .form-group-horizontal input[type="url"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
    }
    .button-horizontal {
        background-color: #0056b3;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    .button-horizontal:hover {
        background-color: #003d80;
    }
    .bes-credits-box, .bes-user-script-box {
        margin-top: 20px;
        padding: 20px;
        border: 1px solid #003366;
        background-color: #e6f2ff;
        border-radius: 8px;
        font-family: 'Arial', sans-serif;
    }
    .credits-value {
        font-size: 24px;
        font-weight: bold;
        color: #003366;
    }
    .bes-user-script-box textarea {
        width: 100%;
        height: 100px;
        padding: 10px;
        font-family: 'Courier New', Courier, monospace;
        box-sizing: border-box;
    }
</style>