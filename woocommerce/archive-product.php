<?php
/**
 * Archive Product Template pour Marketplace d'Expériences
 */

defined( 'ABSPATH' ) || exit;

// Vérifier si nous sommes sur une page boutique de vendeur YITH
$is_vendor_store = false;
if (function_exists('yith_wcmv_is_vendor_page') && yith_wcmv_is_vendor_page()) {
    $is_vendor_store = true;
}

// Si c'est une boutique vendeur, utiliser le template standard
if ($is_vendor_store) {
    // Charger le template vendeur par défaut
    include(WC()->plugin_path() . '/templates/archive-product.php');
    return;
}

// Sinon, continuer avec votre template personnalisé
get_header( 'shop' );

// Votre code personnalisé ici...
?>
<div class="experience-marketplace-banner">
    <div class="banner-content">
        <h1>Explorez nos expériences à offrir</h1>
    </div>
</div>

<?php do_action( 'woocommerce_before_main_content' ); ?>

<div class="experience-marketplace-container">
    <!-- Sidebar / Filtres -->
    <aside class="experience-sidebar">
        <div class="sidebar-inner">
            <div class="sidebar-block search-block">
                <h3>Rechercher</h3>
                <form role="search" method="get" class="experience-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <div class="search-field-container">
                        <input type="search" id="experience-search-field" class="search-field" 
                            placeholder="Quelle expérience recherchez-vous ?" 
                            value="<?php echo get_search_query(); ?>" name="s" autocomplete="off" />
                        <button type="submit" class="search-submit">
                            <span class="screen-reader-text">Rechercher</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </button>
                    </div>
                    <input type="hidden" name="post_type" value="product" />
                </form>
                <div class="live-search-results"></div>
            </div>

            <div class="sidebar-block filter-block">
                <h3>Filtrer par catégorie</h3>
                <form action="" method="get">
                    <select name="product_cat" onchange="this.form.submit()">
                        <option value="">Toute catégorie</option>
                        <?php
                        $args = array(
                            'taxonomy'   => 'product_cat',
                            'hide_empty' => true,
                            'parent'     => 0,
                        );
                        $product_categories = get_terms($args);
            
                        if (!empty($product_categories)) {
                            foreach ($product_categories as $category) {
                                // Détecter la catégorie sélectionnée dans l'URL
                                $selected = (isset($_GET['product_cat']) && $_GET['product_cat'] == $category->slug) ? 'selected' : '';
                                echo '<option value="' . esc_attr($category->slug) . '" ' . $selected . '>' . esc_html($category->name) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </form>
            </div>


            <div class="sidebar-block price-block">
                <div class="price-range-filter">
                    <?php
                    if (class_exists('WC_Widget_Price_Filter')) {
                        the_widget('WC_Widget_Price_Filter', array(), array(
                            'before_title'  => '<h3 class="widget-title">',
                            'after_title'   => '</h3>'
                        ));
                    }
                    ?>
                </div>
            </div>

            <?php if (is_active_sidebar('shop-sidebar')) : ?>
                <?php dynamic_sidebar('shop-sidebar'); ?>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Zone principale des produits -->
    <main class="experience-products-area">
        <div class="experience-toolbar">
            <div class="result-count">
                <?php woocommerce_result_count(); ?>
            </div>
            <div class="experience-sorting">
                <?php woocommerce_catalog_ordering(); ?>
            </div>
        </div>

        <?php if (woocommerce_product_loop()) : ?>
            <div class="experience-products-grid">
                <?php
                woocommerce_product_loop_start();
                
                if (wc_get_loop_prop('total')) {
                    while (have_posts()) {
                        the_post();
                        wc_get_template_part('content', 'product');
                    }
                }
                
                woocommerce_product_loop_end();
                ?>
            </div>

            <div class="experience-pagination">
                <?php do_action('woocommerce_after_shop_loop'); ?>
            </div>
        <?php else : ?>
            <div class="no-experiences-found">
                <?php do_action('woocommerce_no_products_found'); ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php
do_action('woocommerce_after_main_content');
get_footer('shop');