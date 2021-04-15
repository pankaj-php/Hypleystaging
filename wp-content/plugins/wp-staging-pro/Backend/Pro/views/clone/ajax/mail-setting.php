<p>
    <?php _e('Toggle emails sending for the staging site', 'wp-staging'); ?>
</p>
<?php
if (empty($options->current)) {
    /*
     * New staging site.
     * Mails Sending is checked by default.
     */
    ?>
    <div class="wpstg-form-group">
        <label class="wpstg-checkbox" for="wpstg_allow_emails">
            <?php _e('Allow Mails Sending:', 'wp-staging'); ?> <input type="checkbox" name="wpstg_allow_emails" id="wpstg_allow_emails" checked>
        </label>
    </div>

    <?php
} else {
    /*
     * Existing staging site.
     * We read the site configuration. If none set, default to checked, since not having the setting
     * to allow the email in the database means it was not disabled.
     */
    // To support staging site created with older version of this feature,
    // Invert it's value if it is present
    // Can be removed when we are sure that all staging sites have been updated.
    $defaultEmailsSending = true;
    if (isset($options->existingClones[$options->current]['emailsDisabled'])) {
        $defaultEmailsSending = !((bool)$options->existingClones[$options->current]['emailsDisabled']);
    }

    $emailsAllowed = isset($options->existingClones[$options->current]['emailsAllowed']) ? (bool) $options->existingClones[$options->current]['emailsAllowed'] : $defaultEmailsSending;
    ?>

    <div class="wpstg-form-group">
        <label class="wpstg-checkbox" for="wpstg_allow_emails">
            <?php _e('Allow Mails Sending:', 'wp-staging'); ?> <input type="checkbox" name="wpstg_allow_emails" id="wpstg_allow_emails" <?php echo $emailsAllowed === true ? 'checked' : '' ?>>
        </label>
    </div>

<?php } ?>

<hr/>
