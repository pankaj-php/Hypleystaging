<?php
/**
 * @var $this \WPStaging\Backend\Pro\Notices\Notices
 * @see \WPStaging\Backend\Pro\Notices\Notices::getNotices
 */
?>
<div class="notice wpstg-mails-notice" style="border-left: 4px solid #ffba00;">
    <p>
        <strong style="margin-bottom: 10px;"><?php _e('Mails Disabled', 'wp-staging'); ?></strong> <br/>
        <?php _e('WP STAGING has disabled those outgoing mails which depends upon wp_mail service on this staging site by your permission during the cloning process.', 'wp-staging'); ?> <br/>
        <b><?php _e('Note', 'wp-staging') ?>: </b> <?php echo sprintf(__('Some plugins might still be able to send out mails if they don\'t depend upon %s.', 'wp-staging'), '<code>wp_mail()</code>', '<code>wp_mail()</code>', '<strong>WP STAGING</strong>'); ?>
    </p>
    <p>
        <a href="javascript:void(0);" class="wpstg_hide_disabled_mail_notice" title="<?php _e('Close this message', 'wp-staging') ?>"
            style="font-weight:bold;">
            <?php _e('Close this message', 'wp-staging') ?>
        </a>
    </p>
</div>
<script>
  jQuery(document).ready(function ($) {
    jQuery(document).on('click', ".wpstg_hide_disabled_mail_notice", function (e) {
      e.preventDefault();

      jQuery.ajax({
        url: ajaxurl,
        type: "POST",
        data: { action: "wpstg_hide_disabled_mail_notice" },
        error: function(xhr, textStatus, errorThrown) {
          console.log(xhr.status + ' ' + xhr.statusText + '---' + textStatus);
          console.log(textStatus);

          alert(
            "Unknown error. Please get in contact with us to solve it support@wp-staging.com"
          );
        },
        success: function(data) {
          jQuery(".wpstg-mails-notice").slideUp("fast");
          return true;
        },
        statusCode: {
          404: function() {
            alert("Something went wrong; can't find ajax request URL! Please get in contact with us to solve it support@wp-staging.com");
          },
          500: function() {
            alert("Something went wrong; internal server error while processing the request! Please get in contact with us to solve it support@wp-staging.com");
          }
        }
      });

    })
  });
</script>
