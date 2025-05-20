<?php
/**
 * Template pour l'affichage des cartes de produits d'expériences
 *
 * @package Astra Child
 * @since 1.0.0
 */

defined('ABSPATH') || exit;

global $product;

// S'assurer que $product est bien défini
if (!$product || !($product instanceof WC_Product)) {
    $product = wc_get_product(get_the_ID());
}

// Si on n'a toujours pas de produit valide, sortir
if (!$product || !($product instanceof WC_Product)) {
    return;
}

// Obtenir les informations sur le vendeur si YITH Multi-Vendor est installé
$vendor_name = '';
if (function_exists('yith_get_vendor')) {
    $vendor = yith_get_vendor($product->get_id(), 'product');
    if ($vendor && method_exists($vendor, 'get_name')) {
        $vendor_name = $vendor->get_name();
    }
}

// Obtenir la catégorie principale
$category_name = '';
$categories = get_the_terms($product->get_id(), 'product_cat');
if (!empty($categories) && !is_wp_error($categories)) {
    $category_name = $categories[0]->name;
}
?>

<div <?php wc_product_class('experience-card', $product); ?>>
    <div class="experience-card-inner">
        <!-- Badge de catégorie -->
        <?php if ($category_name) : ?>
            <div class="experience-category-badge">
                <?php echo esc_html($category_name); ?>
            </div>
        <?php endif; ?>

        <!-- Image du produit avec effet de zoom -->
        <a href="<?php the_permalink(); ?>" class="experience-image-link">
            <div class="experience-image">
                <?php
                if (has_post_thumbnail()) {
                    the_post_thumbnail('medium_large');
                } else {
                    echo wc_placeholder_img('medium_large');
                }
                ?>
                <div class="experience-overlay">
                    <span class="view-details">Voir les détails</span>
                </div>
            </div>
        </a>

        <div class="experience-card-content">
            <!-- En-tête avec titre et prix -->
            <div class="experience-header">
                <h2 class="experience-title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <div class="experience-price">
                    <?php echo $product->get_price_html(); ?>
                </div>
            </div>

            <!-- Description courte -->
            <div class="experience-excerpt">
                <?php 
                $short_description = $product->get_short_description();
                if (!empty($short_description)) {
                    echo wp_trim_words($short_description, 24, '...');
                } else {
                    $content = get_the_content();
                    echo wp_trim_words($content, 24, '...');
                }
                ?>
            </div>

            <!-- Pied de carte avec vendeur et bouton -->
            <div class="experience-footer">
                <?php if ($vendor_name) : ?>
                    <div class="experience-vendor">
                        <span class="vendor-label">Par</span> <?php echo esc_html($vendor_name); ?>
                    </div>
                <?php endif; ?>

                <div class="experience-actions">
                    <a href="<?php the_permalink(); ?>" class="experience-details-button">
                        Découvrir
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>