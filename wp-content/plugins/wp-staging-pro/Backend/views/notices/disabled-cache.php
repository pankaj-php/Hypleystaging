<?php
/**
 * @var $this \WPStaging\Backend\Notices\Notices
 * @see \WPStaging\Backend\Notices\Notices::showNotices
 */
?>
<div class="notice wpstg-cache-notice" style="border-left: 4px solid #ffba00;">
    <p>
        <strong style="margin-bottom: 10px;"><?php _e('Cache Disabled', 'wp-staging'); ?></strong> <br/>
        <?php _e('WP STAGING disabled the cache on this staging site by setting the constant WP_CACHE to false in the wp-config.php.', 'wp-staging'); ?>
    </p>
    <p>
        <a href="javascript:void(0);" class="wpstg_hide_cache_notice" title="Close this message"
            style="font-weight:bold;">
            <?php _e('Close this message', 'wp-staging') ?>
        </a>
    </p>
</div>
<script>
  jQuery(document).ready(function ($) {
    jQuery(document).on('click', '.wpstg_hide_cache_notice', function (e) {
      e.preventDefault();
      jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
          action: 'wpstg_hide_cache_notice'
        },
        error: function error(xhr, textStatus, errorThrown) {
          console.log(xhr.status + ' ' + xhr.statusText + '---' + textStatus);
          console.log(textStatus);
          alert('Unknown error. Please get in contact with us to solve it support@wp-staging.com');
        },
        success: function success(data) {
          jQuery('.wpstg-cache-notice').slideUp('fast');
          return true;
        },
        statusCode: {
          404: function _() {
            alert('Something went wrong; can\'t find ajax request URL! Please get in contact with us to solve it support@wp-staging.com');
          },
          500: function _() {
            alert('Something went wrong; internal server error while processing the request! Please get in contact with us to solve it support@wp-staging.com');
          }
        }
      });
    });
  });
</script>
