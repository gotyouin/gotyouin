<?php if( !isset($_GET['app']) && !isset( $_COOKIE['gotyouin_mobile_app' ] ) ) { 
?>
<section id="large-header-block">
<header id="first-header">
    <div class="container">
      <a data-target=".nav-collapse" data-toggle="collapse" class="btn btn-navbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>
      <?php if ($page['header']): ?>
        <?php print render($page['header']); ?>
      <?php endif; ?>
      <h1 class="span8"><a href="/"><img src="<?php echo DBS_STATIC;?>/sites/all/themes/bh_bootstrap/images/GotYouIn.png" alt="" /></a><span class="hidden">Got You In</span></h1>
      <?php if($is_front){ ?><p class="span8 intro"><span class="tagline">Your Cut | Your 
Time<br/><small>The FIRST universal scheduling app specifically for barbers and their customers</span></small></p><?php } ?>
    </div>
    <div class="strip" style="position: relative;">
      <div class="container">
        <span>
          <a href="https://itunes.apple.com/us/app/got-you-in/id602498495?mt=8"><img src="<?php echo DBS_STATIC;?>/sites/all/themes/bh_bootstrap/images/btn-appstore" alt=""></a>
          <a href="https://play.google.com/store/apps/details?id=com.dbsinteractive.gotyouin"><img src="<?php echo DBS_STATIC;?>/sites/all/themes/bh_bootstrap/images/btn-googleplay" alt=""></a>
        </span>
      </div>
    </div>
</header>
</section>
<?php } ?>
