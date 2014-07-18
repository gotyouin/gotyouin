<?php

/**
 * This file controls the cancel-appointment page
 * Based on default theme implementation to display a single Drupal page.
 * @author Jenny Chalek 2013-03-22
 * 
 * 469 is the staging node
 * change filename to 1178 for live
 */
?>

<?php include('header.php'); ?>
<?php //we will need this helper file to process paid subscription cancellations
require_once('includes/PayflowNVPAPI.php'); ?>

<div class="container">
  <?php /*
  <?php if ($breadcrumb): ?>
  <?php print $breadcrumb; ?>
  <?php endif; ?>
  */ ?>

  <?php if( arg(0) == 'shops'): ?>
  <?php endif; ?>

  <?php print $messages; ?>
  <?php if ($page['highlighted']): ?>
  <?php print render($page['highlighted']); ?>
  <?php endif; ?>
  <div class="row">
    <?php if ($page['sidebar_first']): ?>
    <div class="span<?php print $sidebar_first_size; ?>">
      <?php print render($page['sidebar_first']); ?>
    </div>
    <?php endif; ?>  
    <div class="span<?php print $content_size; ?>">
      <?php print render($title_prefix); ?>
      <?php if ($title && !$page['highlighted'] && arg(0) != 'user'): ?>
      <h1 class="title" id="page-title"><?php print $title; ?></h1>
      <?php elseif ($title && $page['highlighted'] ): ?>
      <h2 class="title" id="page-title"><?php print $title; ?></h2>
      <?php endif; ?>
      <?php print render($title_suffix); ?>
      <?php if ($tabs['#primary'] != ''): ?><div class="tabs"><?php print render($tabs); ?></div><?php endif; ?>
      <?php print render($page['help']); ?>
      <?php if ($action_links): ?><ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?>
      <?php //print render($page['content']); ?>
      
      <?php //form evaluation code block 

if ($_POST['submit'] == "Yes, Cancel") {
     cancelAccount(); 
} elseif ($_POST['submit'] == "Never Mind") {
     drupal_goto("/user");
} else {
?>
      <form method="POST">
           <h4>Are you sure you wish to cancel your account?</h4>
           <input type="submit" name="submit" value="Never Mind">
           <input type="submit" name="submit" value="Yes, Cancel">
      </form>
<?php }   
//------------------------------------------------------------------------------

function cancelAccount() {
     global $user;
     $uprofiles = profile2_load_by_user($user->uid);
     if (!empty($uprofiles)) {
          if (array_key_exists('shop_owner',$uprofiles)) {
               $uprofile = $uprofiles['shop_owner'];
          } elseif (array_key_exists('independent_barber',$uprofiles)) {
               $uprofile = $uprofiles['independent_barber'];
          }
     }
     
     $request = array(
              "PARTNER" => "PayPal",
              /* ------------------------------------------- */
              /* replace with real account info to go live */
              "VENDOR" => "johnmark",
              "USER" => "johnmark",
              "PWD" => "litm0112",
              /* ------------------------------------------- */
              "TRXTYPE" => "R",
              "ACTION" => "C",
              "ORIGPROFILEID" => $uprofile->field_paypalprofile['und']['0']['value'],
          );
 
          $result = run_payflow_call($request);
          
          //safety valve so we don't accidentally deactivate the admin accounts
          if ($user->uid != 1 && $user->uid != 34) {
               user_block_user_action($user);
          }
          $uprofile->field_subscription_status = 'CANCELED';
          profile2_save($uprofile);
          echo "Your account is canceled. You will not be billed again.";
          $msg = "Your GotYouIn account has been canceled. ";
          $msg .= "You will not be billed any additional payments.<br /><br />";
          $msg .= "If you did not mean to cancel, please call customer ";
          $msg .= "service at 502-414-1541 for assistance.<br /><br />";
          mailReminder('GotYouIn Account Canceled', $msg);
}

function mailReminder($subject, $msg) {
     global $user;
     $display = $user->field_first_name['und']['0']['value'];
     $display .= " ".$user->field_last_name['und']['0']['value'];
     
     $to = $user->mail;
     $from = "Got You In <noreply@gotyouin.com>";
     
     $body = "Dear ".$display.":<br /><br />";
     $body .= $msg;
     $body .= "Sincerely, Got You In!";
     
     $params = array(
         'subject' => $subject,
         'body' => $body,
         'from' => $from,
         'headers' => array(
             'From' => $from,
             'Sender' => $from,
             'Return-Path' => $from,
             'Bcc' => 'jenny@dbswebsite.com',
             'Bcc' => 'hal@garth2.resultsbydesign.com',
         ),
     );
     
     $result = drupal_mail(
          'htmlmail', 'reminder', $to, language_default(), $params
     );
}

//------------------------------------------------------------------------------?>
      
      <?php print $feed_icons; ?>      
    </div> 
    <?php if ($page['sidebar_second']): ?>
    <div class="span<?php print $sidebar_second_size; ?>">
      <?php print render($page['sidebar_second']); ?>
    </div>
    <?php endif; ?>
  </div><!-- .row -->
</div>  
<?php //if ($page['row_post_content']): print render($page['row_post_content']); endif; ?>
<?php include('footer.php'); ?>
<script>
	<?php // FIXME these are admin only checkboxes and are hidden here temporarily 2013-02-06 ?>
	jQuery( function() {
		
	});
</script>