<?php
defined( 'YITH_WPV_INIT' ) || exit; // Exit if accessed directly.
?>

<div class="<?php echo $store_description_class ?>">
    <h3 class="store-subtitles">DESCRIPTION</h3>
    <?php echo wpautop( $vendor_description ); ?>
</div>
