<?php
/**
 * GoNorth Custom Footer
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<footer class="gn-footer">
  <div class="gn-footer__inner">

    <div class="gn-footer__brand">
      <a href="<?php echo esc_url( home_url('/') ); ?>" class="gn-footer__logo">כיוון צפון</a>
      <p>המדריך המלא לטיול בצפון ישראל — אטרקציות, לינה, אוכל וסיורים בגליל, גולן וחרמון.</p>
    </div>

    <div class="gn-footer__col">
      <h4>קטגוריות</h4>
      <a href="<?php echo home_url('/places/category/atraktziot-main/'); ?>">אטרקציות וטבע</a>
      <a href="<?php echo home_url('/places/category/accommodation/'); ?>">לינה וצימרים</a>
      <a href="<?php echo home_url('/places/category/restaurants-sub/'); ?>">אוכל ויקבים</a>
      <a href="<?php echo home_url('/places/category/tours/'); ?>">סיורים ופעילויות</a>
    </div>


    <div class="gn-footer__col">
      <h4>כיוון צפון</h4>
      <a href="<?php echo home_url('/about/'); ?>">אודות</a>
      <a href="<?php echo esc_url( home_url('/contact/') ); ?>">צור קשר</a>
      <a href="<?php echo esc_url( home_url('/contact/') ); ?>">דיווח על טעות</a>
      <a href="<?php echo esc_url( home_url('/negishot/') ); ?>">הצהרת נגישות</a>
    </div>

  </div>

  <div class="gn-footer__bottom">
    <span>© <?php echo date('Y'); ?> gonorth.co.il — כל הזכויות שמורות</span>
    <span>מדריך לצפון ישראל</span>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>


