<?php
/**
 * @package JaringLead
 * @version 0.0.1
 */

/*
Plugin Name: JaringLead
Plugin URI: http://mafadev.com/jaringlead
Description: This is just wordpress plugin to grep new lead from your website
Author: Nazala Maslikhan Mafa
Version: 0.0.1
Author URI: http://mafadev.com/nazala
*/

if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'jaringlead_add_menu');
function jaringlead_add_menu() {
    add_menu_page(
        'Jaringlead', 
        'Jaringlead',
        'manage_options',
        'jaringlead',
        'jaringlead_page',
        'dashicons-admin-site',
        3
    );
}

function jaringlead_page_old() {
    ?>
        <div class="jaringlead container mt-5">
            <h1>Jaringlead - Daftar Lead</h1>
            <table id="jaringlead-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Phone</th>
                        <th>Created At</th>
                    </tr>
                </thead>
            </table>
        </div>
    <?php
}

function jaringlead_page() {
    // Simpan data kalau form disubmit
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_admin_referer('jaringlead_save_options')) {
        $pixel_id   = sanitize_text_field($_POST['jaringlead_pixel_id']);
        $phone   = sanitize_text_field($_POST['jaringlead_cs_phone']);
        $message = sanitize_textarea_field($_POST['jaringlead_cs_response_message']);

        // Validasi nomor HP: hanya angka dan panjang minimal 8 digit
        if (!preg_match('/^[0-9]{8,15}$/', $phone)) {
            echo '<div class="error"><p>Nomor HP tidak valid. Minimal 8 digit angka.</p></div>';
        } elseif (empty($message)) {
            echo '<div class="error"><p>Pesan tidak boleh kosong.</p></div>';
        } else {
            update_option('jaringlead_pixel_id', $pixel_id);
            update_option('jaringlead_cs_phone', $phone);
            update_option('jaringlead_cs_response_message', $message);
            echo '<div class="updated"><p>Pengaturan berhasil disimpan!</p></div>';
        }
    }

    // Ambil nilai lama
    $saved_pixel_id   = esc_attr(get_option('jaringlead_pixel_id', ''));
    $saved_phone   = esc_attr(get_option('jaringlead_cs_phone', ''));
    $saved_message = esc_textarea(get_option('jaringlead_cs_response_message', ''));
    ?>

    <div class="wrap">
        
        <div class="container">
            <h1>Jaringlead - Daftar Lead</h1>

            <h2>Pengaturan Customer Service</h2>
            <form method="post" action="">
                <?php wp_nonce_field('jaringlead_save_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="jaringlead_pixel_id">ID FB Pixel</label></th>
                        <td>
                            <input type="text" id="jaringlead_pixel_id" name="jaringlead_pixel_id" 
                                value="<?php echo $saved_pixel_id; ?>" class="regular-text" required />
                            <p class="description">Masukkan ID FB pixel.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="jaringlead_cs_phone">Nomor HP CS</label></th>
                        <td>
                            <input type="text" id="jaringlead_cs_phone" name="jaringlead_cs_phone" 
                                value="<?php echo $saved_phone; ?>" class="regular-text" required />
                            <p class="description">Masukkan nomor HP (hanya angka, minimal 8 digit).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="jaringlead_cs_response_message">Pesan Respon</label></th>
                        <td>
                            <textarea id="jaringlead_cs_response_message" name="jaringlead_cs_response_message" 
                                    rows="5" class="large-text" required><?php echo $saved_message; ?></textarea>
                            <p class="description">Pesan balasan yang akan dikirim ke user, {name} dan {phone} akan diubah dengan data yang akan diinputkan lead.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Simpan Pengaturan'); ?>
            </form>
        </div>

        <!-- TABLE LEADS -->
        <div class="jaringlead container mt-5">
            <h2>Daftar Lead</h2>
            <table id="jaringlead-table" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Phone</th>
                        <th>Created At</th>
                    </tr>
                </thead>
            </table>
        </div>

    </div>

    <?php
}


add_action('admin_enqueue_scripts', 'jaringlead_enqueue_assets');
function jaringlead_enqueue_assets($hook) {
    if ($hook !== 'toplevel_page_jaringlead') return;

    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
    
    wp_enqueue_style('datatables-css', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array('bootstrap-css'));

    wp_enqueue_style('datatables-bs5-css', 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css', array('bootstrap-css'));
    
    wp_enqueue_style('jaringlead-css', plugin_dir_url(__FILE__) . 'index.css', array('datatables-css', 'bootstrap-css', 'datatables-bs5-css'));

    wp_enqueue_script('jquery');

    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);

    wp_enqueue_script('datatables-js', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), null, true);

    wp_enqueue_script('datatables-bs5-js', 'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js', array('jquery', 'datatables-js'), null, true);

    wp_enqueue_script('jaringlead-js', plugin_dir_url(__FILE__) . 'index.js', array('jquery', 'bootstrap-js', 'datatables-js', 'datatables-bs5-js'), false, true);
}

add_action('rest_api_init', function () {
    register_rest_route('jaringlead/v1', '/leads', array(
        'methods' => 'GET',
        'callback' => 'jaringlead_api_hello',
        'permission_callback' => '__return_true', // sementara tanpa auth
    ));
});
function jaringlead_api_hello(WP_REST_Request $request) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'jaringlead_leads';
    $results = $wpdb->get_results("SELECT name, phone, created_at FROM $table_name ORDER BY created_at DESC", ARRAY_A);
    return array(
        'status' => 'success',
        'data' => $results
    );
}

register_activation_hook(__FILE__, 'jaringlead_create_table');
function jaringlead_create_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'jaringlead_leads';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = <<<SQL
        CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NULL,
            phone VARCHAR(255) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;
    SQL;

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_shortcode('jaringlead_form', function() {
    wp_enqueue_style('jaringlead-form-css', plugin_dir_url(__FILE__) . 'form.css');
    ob_start();

    $saved_phone   = esc_attr(get_option('jaringlead_cs_phone', ''));
    $saved_message = esc_textarea(get_option('jaringlead_cs_response_message', ''));

    $saved_message = preg_replace('/\{(\w+)\}/', '${data.$1}', $saved_message);

    ?>
    <?php require_once(dirname(__FILE__) . '/form.html'); ?>

    <div id="jaringlead-response"></div>
    <script>
        document.getElementById('jaringlead-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            const message = `<?php echo $saved_message ?>`;

            const res = await fetch('<?php echo esc_url( rest_url('jaringleads/v1/lead') ); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const json = await res.json();
            
            if (json.status === 'success') {
                window.location.href = 'https://api.whatsapp.com/send/?phone=<?php echo $saved_phone ?>&text=' + message + '&app_absent=0';
            }
        });
    </script>
    <?php
    return ob_get_clean();
});

add_action('rest_api_init', function() {
    register_rest_route('jaringleads/v1', '/lead', [
        'methods' => 'POST',
        'callback' => 'jaringlead_save_lead',
        'permission_callback' => '__return_true'
    ]);
});
function jaringlead_save_lead($request) {
    global $wpdb;
    $table = $wpdb->prefix . 'jaringlead_leads';

    $name  = sanitize_text_field($request['name']);
    $phone = sanitize_text_field($request['phone']);

    $success = $wpdb->insert($table, [
        'name' => $name,
        'phone' => $phone,
        'created_at' => current_time('mysql')
    ]);

    return [ 
        'status'    => 'success', 
        'message'   => 'Lead saved successfully!' ,
        'data'      => compact('name', 'phone', 'success'),
    ];
}
