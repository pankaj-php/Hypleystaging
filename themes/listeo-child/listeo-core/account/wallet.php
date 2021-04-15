<!-- Content -->
<?php if(isset($data)) : 
	$commissions = $data->commissions; 
	$payouts = $data->payouts; 
	?>
<?php endif; 
$current_user = wp_get_current_user(); ?>
<div class="row" id="waller-row" data-numberFormat= <?php if(wc_get_price_decimal_separator() == ',') { echo 'euro'; } ?>>
	<?php 
	$balance = 0;

	foreach ($commissions as $commission) { 
		if($commission['status'] == "unpaid") :
			if($order){
			$order = wc_get_order( $commission['order_id'] );
			$total = $order->get_total();
			$earning = (float) $total - $commission['amount'];
			$balance = $balance + $earning;	
			}
			
		endif;
	}
	
	
	// if (wc_get_price_decimal_separator() == ',') {
	// 	$data->earnings_total = number_format( $data->earnings_total, 2, ',', ' ' );
	// }
	// echo $data->earnings_total;

	 ?>
	<!-- Item -->
	<div class="col-lg-4 col-md-6">
		<div class="dashboard-stat color-1">,
			<div class="dashboard-stat-content wallet-totals"><h4><?php echo wc_price($balance,array('currency'=>' ','decimal_separator' => '.' )); ?></h4> <span><?php esc_html_e('Withdrawable Balance','listeo_core') ?> <strong class="wallet-currency"><?php echo get_option('listeo_currency'); ?></strong></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Money-2"></i></div>
		</div>
	</div>
	<!-- Item -->
	<div class="col-lg-4 col-md-6">
		<div class="dashboard-stat color-3">
			<div class="dashboard-stat-content wallet-totals"><h4><?php echo wc_price($data->earnings_total,array('currency'=>' ','decimal_separator' => '.' )); ?></h4> <span><?php esc_html_e('Total Earnings','listeo_core'); ?> <strong class="wallet-currency"><?php echo get_option('listeo_currency'); ?></strong></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Money-Bag"></i></div>
		</div>
	</div>

	<!-- Item -->
	<div class="col-lg-4 col-md-6">
		<div class="dashboard-stat color-2">
			<div class="dashboard-stat-content"><h4><?php echo $data->total_orders; ?></h4> <span><?php esc_html_e('Total Orders','listeo_core'); ?></span></div>
			<div class="dashboard-stat-icon"><i class="im im-icon-Shopping-Cart"></i></div>
		</div>
	</div>

</div>
<!-- Invoices -->
<div class="row">
	<div class="col-lg-6 col-md-12">
		<div class="dashboard-list-box invoices with-icons margin-top-20">
			<h4><?php esc_html_e('Earnings','listeo_core') ?> <div class="comission-taken"><?php esc_html_e('Fee','listeo_core'); ?>: <strong><?php echo get_option('listeo_commission_rate',10) ?>%</strong></div></h4>
			<?php if($commissions) {?>
			<ul>
				<?php 
				foreach ($commissions as $commission) { 

					$order = wc_get_order( $commission['order_id'] );
					if($order):
					$total = $order->get_total();
					$earning = $total - $commission['amount'];
					?>
					<li class="commission-<?php echo $commission['status']; ?>"><i class="list-box-icon sl sl-icon-basket"></i>
						<strong><?php echo get_the_title($commission['listing_id']) ?></strong>
						<?php if($commission['status'] == 'paid') { ?> <span class="commission-tag-paid"><?php esc_html_e('Processed','listeo_core'); ?></span> <?php } ?>
						<ul>
							<li class="paid"><?php echo wc_price($total); ?></li>
							<li class="unpaid"><?php esc_html_e('Fee','listeo_core'); ?>: <?php echo wc_price($commission['amount']); ?></li>
							<li class="paid"><?php esc_html_e('Your Earning','listeo_core'); ?>: <span><?php echo wc_price($earning); ?></span></li>
							<li><?php esc_html_e('Order','listeo_core'); ?>: #<?php echo $commission['order_id']; ?></li>
							<li><?php esc_html_e('Date','listeo_core'); ?>: <?php echo date(get_option( 'date_format' ), strtotime($commission['date']));  ?></li>
						</ul>
					</li>
				<?php endif;
				} ?>
			</ul>
		<?php } else { ?>
			<ul>
				<li class="wallet-empty-list"><i class="list-box-icon sl sl-icon-basket"></i><?php esc_html_e('You don\'t have any earnings yet','listeo_core'); ?></li>
			</ul>
		<?php } ?>
		</div>
	</div>
						
			<!-- Invoices -->
	<div class="col-lg-6 col-md-12">
		<?php $payment_type =  (isset($current_user->listeo_core_payment_type)) ? $current_user->listeo_core_payment_type : '' ; ?>

		<!-- PAYOUT METHOD POPUP -->
		<div id="small-dialog" class="zoom-anim-dialog mfp-hide">
			<div class="small-dialog-header">
				<h3><?php esc_html_e('Payout Method','listeo_core'); ?></h3>
			</div>
			<div class="message-reply margin-top-0">
				<form method="post" id="edit_user" action="<?php the_permalink(); ?>">
				<!-- Payment Methods Accordion -->
				<div class="payment payout-method-tabs">

					<div class="payment-tab <?php if(empty($payment_type) || $payment_type == 'paypal') { ?>payment-tab-active <?php } ?>">
						<div class="payment-tab-trigger">
							<input <?php checked($payment_type,'paypal') ?> id="paypal" name="payment_type" type="radio" value="paypal">
							<label for="paypal"><?php esc_html_e('PayPal','listeo_core'); ?></label>
						</div>

						<div class="payment-tab-content">
							<div class="row">
								<div class="col-md-12">
									<div class="card-label">
										<label for="ppemail"><?php esc_html_e('PayPal Email','listeo_core'); ?></label>
										<input id="ppemail" name="ppemail" value="<?php if(isset($current_user->listeo_core_ppemail)) { echo $current_user->listeo_core_ppemail; } ?>"  type="email">
									</div>
								</div>
							</div>
						</div>
					</div>


					<div class="payment-tab <?php if( $payment_type == 'banktransfer') { ?>payment-tab-active <?php } ?> ">
						<div class="payment-tab-trigger">
							<input <?php checked($payment_type,'banktransfer') ?> type="radio" name="payment_type" id="creditCart" value="banktransfer">
							<label for="creditCart"><?php esc_html_e('Bank Transfer','listeo_core'); ?></label>
						</div>

						<div class="payment-tab-content">
							<div class="row">

								<div class="col-md-12">
									<div class="notice notification payout-method-notification"><strong><?php esc_html_e('Add following bank transfer details:','listeo_core'); ?></strong> <?php esc_html_e('account','listeo_core'); ?> <?php esc_html_e('holders name & address, account number, bank name, IBAN, BIC/SWIFT','listeo_core'); ?></div>
									<div class="card-label">
										<label for="cvv"><?php esc_html_e('Bank Transfer Details','listeo_core'); ?></label>
										<div>
											<label><?php esc_html_e('Account holder name','listeo_core'); ?></label>
											<input type="text" name="bd_account_name">
										</div>
										<div>
											<label><?php esc_html_e('Bank name','listeo_core'); ?></label>
											<input type="text" name="bd_bank_name">
										</div>
										<div>
											<label><?php esc_html_e('BSB code','listeo_core'); ?></label>
											<input type="text" name="bd_bsbcode">
										</div>
										<div>
											<label><?php esc_html_e('Account number','listeo_core'); ?></label>
											<input type="text" name="bd_account_no">
										</div>
										<!-- <textarea id="cvv" name="bank_details"  type="text"><?php  if(isset($current_user->listeo_core_bank_details)) {  echo $current_user->listeo_core_bank_details; } ?></textarea> -->
									</div>
								</div>

							</div>
						</div>
					</div>

				</div>
				<!-- Payment Methods Accordion / End -->

				<button class="button margin-top-15"><?php esc_html_e('Save','listeo_core') ?></button>
				<input type="hidden" name="my-account-submission" value="1" />
				</form>

			</div>
		</div>


		<div class="dashboard-list-box invoices with-icons margin-top-20">

					<!-- Do <h4> dodaj w inline CSS position: relative -->
			<h4 style="position: relative;"><?php esc_html_e('Payout History','listeo_core') ?> 	<a href="#small-dialog" class="button payout-method popup-with-zoom-anim"><?php esc_html_e('Set Payout Method','listeo_core') ?></a></h4>
			<?php if($payouts) { ?>
			<ul>
				<?php 
				foreach ($payouts as $payout) { 
					?>
					<li><i class="list-box-icon sl sl-icon-wallet"></i>
						<strong><?php echo wc_price($payout['amount']) ?></strong>
						<ul>
							<li class="payment_method"><?php echo ($payout['payment_method']=='paypal') ? esc_html__('PayPal','listeo_core') : esc_html__('Bank Transfer','listeo_core') ; ?></li>
							<li>Date: <?php echo date(get_option( 'date_format' ), strtotime($payout['date']));  ?></li>
						</ul>
					</li>
					

				<?php } ?>
			</ul>
			<?php } else { ?>
			<ul>
				<li class="wallet-empty-list"><i class="list-box-icon sl sl-icon-wallet"></i> <?php esc_html_e('You don\'t have any payouts yet.','listeo_core') ?></li>
			</ul>
			<?php } ?>
		</div>
	</div>
</div>