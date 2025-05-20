<?php
defined( 'YITH_WPV_INIT' ) || exit;

$style = '';
if ( ! empty( $header_image ) ) {
    $style = "background: url({$header_image}) top center; background-size: cover; height: {$header_height}px;";
}
?>

<div id="yith-wcmv-store-header-<?php echo esc_attr( $vendor->get_id() ); ?>" class="store-header-wrapper avatar-box">

    <!-- ✅ BANNIÈRE + NOM BOUTIQUE + BOUTON -->
    <div class="store-avatar-wrapper" style="<?php echo esc_attr( $style ); ?>"></div>
    <div class="store-name-content">
        <span class="store-name">
            <?php echo esc_html( $vendor->get_name() ); ?>
        </span>
            <!-- Bouton "Voir les expériences" -->
        <button class="view-experiences-btn">Voir les expériences</button>
    </div>

    <!-- ✅ INFOS BOUTIQUE + AVATAR + SOCIALS -->
    <div class="store-info-wrapper">
            <h3 class="store-subtitles">INFORMATIONS</h3>

        <div class="store-info-section">
            <?php
            do_action( 'yith_wcmv_vendor_header_store_info', $vendor );
            do_action( 'yith_wcmv_vendor_header_store_socials', $vendor );
            ?>
        </div>
        <?php if ( ! empty( $avatar ) ) : ?>
            <div class="store-avatar-logo-wrapper no-banner">
                <div class="avatar">
                    <?php echo wp_kses_post( $avatar ); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- ✅ DESCRIPTION -->
    <?php do_action( 'yith_wcmv_vendor_header_store_description', $vendor ); ?>
</div>
