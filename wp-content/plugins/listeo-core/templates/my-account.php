<?php
/* Get user info. */
global $wp_roles;
$current_user = wp_get_current_user();
$roles = $current_user->roles;
$role = array_shift( $roles ); 
$template_loader = new Listeo_Core_Template_Loader; 

if ( isset($_GET['updated']) && $_GET['updated'] == 'true' ) : ?> 
	<div class="notification success closeable margin-bottom-35"><p><?php esc_html_e('Your profile has been updated.', 'listeo_core'); ?></p><a class="close" href="#"></a></div> 
<?php endif; ?>
     

<?php if ( !is_user_logged_in() ) : ?>
    <p class="warning">
        <?php esc_html_e('You must be logged in to edit your profile.', 'listeo_core'); ?>
    </p><!-- .warning -->
<?php else : ?>

<div class="row">

		<!-- Profile -->
		<div class="col-lg-6 col-md-12">
			<div class="dashboard-list-box margin-top-0">
				<h4 class="gray"><?php esc_html_e('Profile Details','listeo_core') ?></h4>
				<form method="post" id="edit_user" action="<?php the_permalink(); ?>">
				<div class="dashboard-list-box-static">
					
					<?php 
					$custom_avatar = $current_user->listeo_core_avatar_id;
					$custom_avatar = wp_get_attachment_url($custom_avatar); 
					if(!empty($custom_avatar)) { ?>
					<div 
					data-photo="<?php echo $custom_avatar; ?>" 
					data-name="<?php esc_html_e('Your Avatar', 'listeo_core'); ?>" 
					data-size="<?php echo filesize( get_attached_file( $current_user->listeo_core_avatar_id ) ); ?>" 
					class="edit-profile-photo">
					
					<?php } else { ?>
					<div class="edit-profile-photo">
					<?php } ?>

						<div id="avatar-uploader" class="dropzone">
							<div class="dz-message" data-dz-message><span><?php esc_html_e('Upload Avatar', 'listeo_core'); ?></span></div>
						</div>
						<input class="hidden" name="listeo_core_avatar_id" type="text" id="avatar-uploader-id" value="<?php echo $current_user->listeo_core_avatar_id; ?>" />
					</div>
		
					<!-- Details -->
					<div class="my-profile">
							
							<?php if(get_option('listeo_profile_allow_role_change')): ?>
								<?php if(in_array($role, array('owner','guest'))): ?>
									<label for="role"><?php esc_html_e('Change your role', 'listeo_core'); ?></label>
									<select name="role" id="role">
										<option <?php selected($role,'guest'); ?> value="guest"><?php esc_html_e('Guest','listeo_core') ?></option>
										<option <?php selected($role,'owner'); ?> value="owner"><?php esc_html_e('Owner','listeo_core') ?></option>
									</select>
								<?php endif; ?>
							<?php endif; ?>
							
						  	<label for="first-name"><?php esc_html_e('First Name', 'listeo_core'); ?></label>
			                <input class="text-input" name="first-name" type="text" id="first-name" value="<?php  echo $current_user->user_firstname; ?>" />
						  	
						  	<label for="last-name"><?php esc_html_e('Last Name', 'listeo_core'); ?></label>
			                <input class="text-input" name="last-name" type="text" id="last-name" value="<?php echo $current_user->user_lastname; ?>" />

							<label for="phone"><?php esc_html_e('Phone', 'listeo_core'); ?></label>
							<input class="text-input" name="phone" type="text" id="phone" value="<?php echo $current_user->phone; ?>" type="text">
							
							<?php  if ( isset($_GET['user_err_pass']) && !empty($_GET['user_err_pass'])  ) : ?> 
							<div class="notification error closeable margin-top-35"><p>
								<?php
								switch ($_GET['user_err_pass']) {
								 	case 'error_1':
								 		echo esc_html_e('The Email you entered is not valid or empty. Please try again.','listeo_core');
								 		break;
								 	case 'error_2':
								 		echo esc_html_e('This email is already used by another user, please try a different one.','listeo_core');
								 		break;					 	
								 	
								 	
								 	default:
								 		# code...
								 		break;
								 }  ?>
									
								</p><a class="close" href="#"></a>
							</div> 
							<?php endif; ?>
							<label for="email"><?php esc_html_e('E-mail', 'listeo_core'); ?></label>
			                <input class="text-input" name="email" type="text" id="email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
			                 
							<label for="description"><?php esc_html_e('About me', 'listeo_core'); ?></label>
			               	<?php 
								$user_desc = get_the_author_meta( 'description' , $current_user->ID);
								$user_desc_stripped = strip_tags($user_desc, '<p>'); //replace <p> and <a> with whatever tags you want to keep after the strip
							?>
			                <textarea name="description" id="description" cols="30" rows="10"><?php echo $user_desc_stripped; ?></textarea>

							<!--<label><i class="fa fa-twitter"></i> <?php esc_html_e( 'Twitter', 'listeo_core' ); ?></label>
							<input class="text-input" name="twitter" type="text" id="twitter" value="<?php  echo $current_user->twitter; ?>" />

							<label><i class="fa fa-facebook-square"></i> <?php esc_html_e( 'Facebook', 'listeo_core' ); ?></label>
							<input class="text-input" name="facebook" type="text" id="facebook" value="<?php  echo $current_user->facebook; ?>" />


							<label><i class="fa fa-linkedin"></i> <?php esc_html_e( 'Linkedin', 'listeo_core' ); ?></label>
							<input class="text-input" name="linkedin" type="text" id="linkedin" value="<?php  echo $current_user->linkedin; ?>" />


							<label><i class="fa fa-instagram"></i> <?php esc_html_e( 'Instagram', 'listeo_core' ); ?></label>
							<input class="text-input" name="instagram" type="text" id="instagram" value="<?php  echo $current_user->instagram; ?>" />

							<label><i class="fa fa-youtube"></i> <?php esc_html_e( 'YouTube', 'listeo_core' ); ?></label>
							<input class="text-input" name="youtube" type="text" id="youtube" value="<?php  echo $current_user->youtube; ?>" />

							<label><i class="fa fa-whatsapp"></i> <?php esc_html_e( 'WhatsApp', 'listeo_core' ); ?></label>
							<input class="text-input" name="whatsapp" type="text" id="whatsapp" value="<?php  echo $current_user->whatsapp; ?>" />

							<label><i class="fa fa-skype"></i> <?php esc_html_e( 'Skype', 'listeo_core' ); ?></label>
							<input class="text-input" name="skype" type="text" id="skype" value="<?php  echo $current_user->skype; ?>" /> -->


							<input type="hidden" name="my-account-submission" value="1" />
							<button type="submit" form="edit_user" value="<?php esc_html_e( 'Submit', 'listeo_core' ); ?>" class="button margin-top-20 margin-bottom-20"><?php esc_html_e('Save Changes', 'listeo_core'); ?></button>
					
						<?php endif; ?>
					

					
				</div>
	
						
					</div>
				</div>
			</div>
		

		</form>
		<!-- Change Password -->
			<div class="col-lg-6 col-md-12">
				<div class="dashboard-list-box margin-top-0">
					<h4 class="gray"><?php esc_html_e('Change Password','listeo_core') ?></h4>
					<div class="dashboard-list-box-static">

						<!-- Change Password -->
						<div class="my-profile">
							<div class="row">
								<div class="col-md-12">
									<div class="notification notice margin-top-0 margin-bottom-0">
										<p><?php esc_html_e('Your password should be at least 12 random characters long to be safe','listeo_core') ?></p>
									</div>
								</div>
							</div>
							<?php if ( isset($_GET['updated_pass']) && $_GET['updated_pass'] == 'true' ) : ?> 
								<div class="notification success closeable margin-bottom-35"><p><?php esc_html_e('Your password has been updated.', 'listeo_core'); ?></p><a class="close" href="#"></a></div> 
							<?php endif; ?>

							<?php  if ( isset($_GET['err_pass']) && !empty($_GET['err_pass'])  ) : ?> 
							<div class="notification error closeable margin-bottom-35"><p>
								<?php
								switch ($_GET['err_pass']) {
								 	case 'error_1':
								 		echo esc_html_e('Your current password does not match. Please retry.','listeo_core');
								 		break;
								 	case 'error_2':
								 		echo esc_html_e('The passwords do not match. Please retry..','listeo_core');
								 		break;					 	
								 	case 'error_3':
								 		echo esc_html_e('A bit short as a password, don\'t you think?','listeo_core');
								 		break;					 	
								 	case 'error_4':
								 		echo esc_html_e('Password may not contain the character "\\" (backslash).','listeo_core');
								 		break;
								 	case 'error_5':
								 		echo esc_html_e('An error occurred while updating your profile. Please retry.','listeo_core');
								 		break;
								 	
								 	default:
								 		# code...
								 		break;
								 }  ?>
									
								</p><a class="close" href="#"></a>
							</div> 
							<?php endif; ?>
							<form name="resetpasswordform" action="" method="post">
								<label><?php esc_html_e('Current Password','listeo_core'); ?></label>
								<input type="password" name="current_pass">

								<label for="pass1"><?php esc_html_e('New Password','listeo_core'); ?></label>
								<input name="pass1" type="password">

								<label for="pass2"><?php esc_html_e('Confirm New Password','listeo_core'); ?></label>
								<input name="pass2" type="password">

								<input type="submit" name="wp-submit" id="wp-submit" class="margin-top-20 button" value="<?php esc_html_e('Save Changes','listeo_core'); ?>" />
								
								<input type="hidden" name="listeo_core-password-change" value="1" />
							</form>

						</div>
						
					</div>
				</div>
			</div>
			<?php if ( class_exists( 'plugin_delete_me' ) ) : ?>
				<div class="col-lg-6 col-md-12 delete-account-section margin-top-40">
					<div class="dashboard-list-box margin-top-0">
						<h4 class="gray"><?php esc_html_e('Delete Your Account','listeo_core') ?></h4>
						<div class="dashboard-list-box-static">
							<?php echo do_shortcode( '[plugin_delete_me /]' ); ?>
						</div>
					</div>
				</div>
			<?php endif; ?>

		</div>

		