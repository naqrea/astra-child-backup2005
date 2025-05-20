<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {
    // Charger le style principal du thème enfant
    wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );
}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

// Charge le JS AJAX
function enqueue_live_search_script() {
    wp_enqueue_script(
        'shop-live-search',
        get_stylesheet_directory_uri() . '/js/live-filter-products.js',
        array(),
        '1.0',
        true
    );

    wp_localize_script( 'shop-live-search', 'ajax_object', array(
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));
}
add_action( 'wp_enqueue_scripts', 'enqueue_live_search_script' );

// SUGGESTIONS RECHERCHE PRODUITS
/**
/**
/**
 * Fonction AJAX pour le filtrage en direct des produits
 */
function handle_live_filter_products() {
    // Vérification de sécurité
    check_ajax_referer('filter_products_nonce', 'security');
    
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    // Paramètres de la requête WooCommerce
    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => get_option('posts_per_page'),
        's'              => $query,
    );
    
    // Ajouter les paramètres de tri si présents
    if (isset($_POST['orderby'])) {
        $ordering = WC()->query->get_catalog_ordering_args($_POST['orderby'], $_POST['order']);
        $args['orderby'] = $ordering['orderby'];
        $args['order'] = $ordering['order'];
        
        if (isset($ordering['meta_key'])) {
            $args['meta_key'] = $ordering['meta_key'];
        }
    }
    
    // Ajouter le filtrage par catégorie si présent
    if (isset($_POST['category']) && !empty($_POST['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $_POST['category'],
            ),
        );
    }
    
    // Lancer la requête
    $products_query = new WP_Query($args);
    
    // Nombre total de résultats trouvés
    echo '<div class="hidden-count">' . sprintf(_n('%s produit trouvé', '%s produits trouvés', $products_query->found_posts, 'woocommerce'), number_format_i18n($products_query->found_posts)) . '</div>';
    
    // Boucle d'affichage des produits
    if ($products_query->have_posts()) {
        woocommerce_product_loop_start();
        
        while ($products_query->have_posts()) {
            $products_query->the_post();
            wc_get_template_part('content', 'product');
        }
        
        woocommerce_product_loop_end();
    } else {
        echo '<div class="no-products-found">';
        echo '<p>Aucun produit ne correspond à votre recherche.</p>';
        echo '</div>';
    }
    
    // Réinitialiser les données de publication
    wp_reset_postdata();
    
    die();
}
add_action('wp_ajax_filter_products_live', 'handle_live_filter_products');
add_action('wp_ajax_nopriv_filter_products_live', 'handle_live_filter_products');
/**
 * Enregistrer les scripts nécessaires
 */
function enqueue_live_filter_scripts() {
    // Enregistrer et charger le script JS pour le filtrage en direct
    wp_enqueue_script(
        'live-filter-products',
        get_stylesheet_directory_uri() . '/js/live-filter-products.js',
        array('jquery'),
        '1.0.1',
        true
    );
    
    // Passer les variables nécessaires au script
    wp_localize_script(
        'live-filter-products',
        'ajax_object',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('filter_products_nonce'),
            'site_url' => home_url(),
        )
    );
    
    // Ajouter des styles CSS pour l'indicateur de chargement
    wp_add_inline_style('woocommerce-inline', '
        .loading-products {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            flex-direction: column;
        }
        
        .loading-spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .no-products-found {
            text-align: center;
            padding: 2rem;
            background: #f8f8f8;
            border-radius: 4px;
            margin: 1rem 0;
        }
    ');
}
add_action('wp_enqueue_scripts', 'enqueue_live_filter_scripts');

////////////////////////////////////////
/**
 * 1. Modifier le numéro de voucher pour inclure l'ID de la sous-commande (vendeur)
 */
add_filter('wc_pdf_product_vouchers_generated_voucher_number', 'customize_voucher_number_suffix', 10, 4);
function customize_voucher_number_suffix($number, $prefix, $random, $order_id) {
    // Tenter de récupérer une sous-commande associée (via wp_wc_orders)
    $suborder_id = get_yith_suborder_id_from_main_order($order_id);

    // Si sous-commande trouvée, on l’utilise comme suffixe. Sinon, fallback sur l’ID original.
    $final_id = $suborder_id ? $suborder_id : $order_id;

    return $random . '-' . $final_id;
}

// Fonction utilitaire pour récupérer une sous-commande à partir de la commande principale
function get_yith_suborder_id_from_main_order($main_order_id) {
    global $wpdb;

    // Recherche dans wp_wc_orders pour trouver la sous-commande liée
    return $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}wc_orders WHERE parent_order_id = %d LIMIT 1",
        $main_order_id
    ));
}
/**
 * 2. Afficher le numéro du voucher dans la liste des commissions YITH
 */

// Ajouter une colonne personnalisée "N°Voucher" dans le tableau des commissions
add_filter('yith_wcmv_commissions_list_table_column', 'add_voucher_number_column');
function add_voucher_number_column($columns) {
    $columns['voucher_number'] = __('N°Voucher', 'your-textdomain');
    return $columns;
}

// Afficher le contenu de la colonne "N°Voucher"
add_filter('yith_wcmv_commissions_list_table_col_voucher_number', 'display_voucher_number_in_commission_row', 10, 3);
function display_voucher_number_in_commission_row($commission, $vendor, $column_name) {
    $voucher_number = get_voucher_number_for_commission($commission);
    echo esc_html($voucher_number);
}

// Fonction pour retrouver le numéro de voucher à partir de l’ID de la sous-commande (lié à la commission)
function get_voucher_number_for_commission($commission) {
    global $wpdb;

    // L’ID de commande stocké dans la commission YITH correspond à une sous-commande
    $order_id = $commission->get_order_id();

    // Rechercher un voucher qui se termine par "-{order_id}"
    $voucher_number = $wpdb->get_var($wpdb->prepare(
        "SELECT post_title 
        FROM {$wpdb->posts} 
        WHERE post_type = 'wc_voucher' 
        AND post_title LIKE %s 
        LIMIT 1",
        '%' . '-' . $order_id
    ));

    return !empty($voucher_number) ? esc_html($voucher_number) : '—';
}



// Supprimer les onglets wordpress non nécessaires pour les vendeurs
function hide_admin_menu_for_vendors() {
    // Vérifiez si l'utilisateur est un vendeur
    $vendor = yith_get_vendor('current', 'user');

    if ($vendor->is_valid()) {
        // Masquer les éléments suivants du menu d'administration
        remove_menu_page('index.php');                  // Tableau de bord
        remove_menu_page('edit.php');                   // Articles
        remove_menu_page('upload.php');                 // Médias
        remove_menu_page('edit.php?post_type=page');    // Pages
        remove_menu_page('edit-comments.php');          // Commentaires
        remove_menu_page('themes.php');                 // Apparence
        remove_menu_page('plugins.php');                // Extensions
        remove_menu_page('users.php');                  // Utilisateurs
        remove_menu_page('tools.php');                  // Outils
        remove_menu_page('options-general.php');        // Réglages
        remove_menu_page('edit.php?post_type=product'); // Produits

        // Masquer les éléments suivants de la barre d'administration
        add_action('admin_bar_menu', function($wp_admin_bar) {
            $wp_admin_bar->remove_node('wp-logo');
            $wp_admin_bar->remove_node('hostinger_admin_bar');
            $wp_admin_bar->remove_node('wpseo-menu');
            $wp_admin_bar->remove_node('seedprod_admin_bar');
        }, 999);
    }
}
add_action('admin_menu', 'hide_admin_menu_for_vendors');

// Rediriger les vendeurs après la connexion sur leur dashboard
function redirect_vendors_after_login($redirect_to, $request, $user) {
    // Vérifiez si l'utilisateur est un vendeur
    $vendor = yith_get_vendor($user->ID, 'user');

    if ($vendor->is_valid()) {
        // Rediriger vers la page spécifique
        return admin_url('admin.php?page=yith_wpv_panel');
    }

    return $redirect_to;
}
add_filter('login_redirect', 'redirect_vendors_after_login', 10, 3);



//RAJOUTER OPTIONS SUR BON : 

/**
 * Ajouter un champ personnalisé pour les données WAPF dans les bons
 * 
 * @param array $fields Les champs existants
 * @return array Les champs mis à jour
 */
function custom_wapf_voucher_fields($fields) {
    $fields['wapf_fields'] = array(
        'data_type' => 'property',
        'label'     => __('Options WAPF', 'my-plugin'),
    );
    
    return $fields;
}
add_filter('wc_pdf_product_vouchers_voucher_fields', 'custom_wapf_voucher_fields');

/**
 * Récupérer les valeurs WAPF pour le champ personnalisé
 * 
 * @param mixed $value La valeur par défaut
 * @param \WC_PDF_Product_Vouchers_Voucher $voucher L'objet voucher
 * @return mixed La valeur WAPF
 */
function custom_get_wapf_fields_value($value, $voucher) {
    $order_item = $voucher->get_order_item();
    
    if (!$order_item) {
        return $value;
    }
    
    $order_item_id = $order_item->get_id();
    
    // Récupération des meta WAPF
    global $wpdb;
    $wapf_data = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta 
        WHERE order_item_id = %d AND meta_key LIKE '%wapf_meta%'",
        $order_item_id
    ));
    
    if (empty($wapf_data)) {
        return $value;
    }
    
    // Traitement des données WAPF
    $wapf_values = array();
    foreach ($wapf_data as $data) {
        $meta_value = maybe_unserialize($data->meta_value);
        if (is_array($meta_value)) {
            foreach ($meta_value as $field) {
                if (isset($field['label']) && isset($field['value'])) {
                    $wapf_values[] = $field;
                }
            }
        }
    }
    
    return $wapf_values;
}
add_filter('wc_pdf_product_vouchers_get_wapf_fields', 'custom_get_wapf_fields_value', 10, 2);

/**
 * Fonction auxiliaire pour supprimer les indications de prix des valeurs WAPF
 * 
 * @param string $value La valeur avec potentiellement un prix
 * @return string La valeur sans le prix
 */
function remove_wapf_prices($value) {
    // Version qui prend en compte l'encodage HTML des symboles de devise
    // Par exemple: "SPA (+50.00&euro;)" devient "SPA"
    $value = html_entity_decode($value); // Convertir &euro; en € pour simplifier le traitement
    $value = preg_replace('/\s*\(\+?\s*\d+(?:[.,]\d+)?\s*(?:€|\$|£|¥|\s*euros?)?\)/', '', $value);
    
    // Supprimer les virgules en fin de chaîne (qui peuvent rester après suppression du prix)
    $value = rtrim($value, ', ');
    
    return $value;
}

/**
 * Formater les valeurs WAPF pour l'affichage sur le bon en supprimant les prix
 * 
 * @param mixed $value_formatted La valeur formatée par défaut
 * @return string La valeur WAPF formatée pour l'affichage sans les prix
 */
function custom_format_wapf_fields_value($value_formatted) {
    if (!is_array($value_formatted)) {
        return $value_formatted;
    }
    
    $formatted_output = '';
    
    foreach ($value_formatted as $field) {
        $label = isset($field['label']) ? $field['label'] : '';
        $value = isset($field['value']) ? $field['value'] : '';
        
        // Gestion des valeurs de type array (comme les cases à cocher)
        if (is_array($value)) {
            // Supprimer les prix de chaque élément du tableau
            $value = array_map('remove_wapf_prices', $value);
            $value = implode(', ', $value);
        } else {
            // Supprimer les prix des valeurs simples
            $value = remove_wapf_prices($value);
        }
        
        // Traitement spécial pour les listes d'options séparées par des virgules
        if (strpos($value, ',') !== false) {
            $options = explode(',', $value);
            $options = array_map('trim', $options);
            $options = array_map('remove_wapf_prices', $options); // Re-appliquer au cas où
            $value = implode(', ', $options);
        }
        
        $formatted_output .= sprintf('<strong>%s:</strong> %s<br>', $label, $value);
    }
    
    return $formatted_output;
}
add_filter('wc_pdf_product_vouchers_get_wapf_fields_formatted', 'custom_format_wapf_fields_value');

//REMOVE COLONNE RATE/TAUX DU DASHBOARD commissions
add_filter( 'yith_wcmv_commissions_list_table_column', 'remove_yith_commission_rate_column' );
function remove_yith_commission_rate_column( $columns ) {
	unset( $columns['rate'] );
	return $columns;
}

function custom_hide_yith_mvp_order_status_element() {
    echo '<style>
        /* Cache spécifiquement le petit texte qui contient le statut de la commande */
        td.order_id.column-order_id small[style="display:block;"],
        td.order_id.column-order_id small:contains("Statut commande:"),
        td.order_id.column-order_id small:last-child {
            display: none !important;
        }
    </style>';
    
    // Solution JavaScript complémentaire pour être certain de cibler l'élément correct
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Cible les éléments small qui contiennent le texte "Statut commande"
        $('td.order_id.column-order_id small').each(function() {
            if ($(this).text().indexOf('Statut commande') !== -1) {
                $(this).hide();
            }
        });
    });
    </script>
    <?php
}

// Hooks pour l'administration WordPress
add_action('admin_head', 'custom_hide_yith_mvp_order_status_element');
add_action('admin_footer', 'custom_hide_yith_mvp_order_status_element');

// Hooks pour les pages frontend (dashboard vendeur)
add_action('wp_head', 'custom_hide_yith_mvp_order_status_element');
add_action('wp_footer', 'custom_hide_yith_mvp_order_status_element');

// Hooks spécifiques à YITH Multivendor (si disponibles)
if (function_exists('yith_wcmv_is_vendor_dashboard')) {
    add_action('yith_wcmv_vendor_dashboard_head', 'custom_hide_yith_mvp_order_status_element');
    add_action('yith_wcmv_after_vendor_dashboard', 'custom_hide_yith_mvp_order_status_element');
    
}

function my_custom_scroll() {
    wp_enqueue_script( 'custom-scroll', get_stylesheet_directory_uri() . '/js/custom-scroll.js', array(), null, true );
}
add_action( 'wp_enqueue_scripts', 'my_custom_scroll' );

