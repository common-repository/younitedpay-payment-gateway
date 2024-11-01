<nav class="nav-tab-wrapper">
	<a href="?page=younitedpay_settings" class="nav-tab 
		<?php if ( 'home' === $option ) { ?>
			nav-tab-active
		<?php } ?>">
		<?php echo esc_html__( 'Home', 'wc-younitedpay-gateway' ); ?>
	</a>
	<a href="?page=wc-settings&tab=checkout&section=younitedpay-gateway" class="nav-tab 
		<?php if ( 'settings' === $option ) : ?>
			nav-tab-active
		<?php endif; ?>
	">
		<?php echo esc_html__( 'Settings', 'wc-younitedpay-gateway' ); ?>
	</a>
	<a href="?page=wc-settings&tab=checkout&section=younitedpay-gateway&option=behaviour" class="nav-tab 
		<?php if ( 'behaviour' === $option ) : ?>
			nav-tab-active
		<?php endif; ?>
	">
		<?php echo esc_html__( 'Behaviour', 'wc-younitedpay-gateway' ); ?>
	</a>
	<a href="?page=wc-settings&tab=checkout&section=younitedpay-gateway&option=appearance" class="nav-tab 
		<?php if ( 'appearance' === $option ) : ?>
			nav-tab-active
		<?php endif; ?>
	">
		<?php echo esc_html__( 'Appearance', 'wc-younitedpay-gateway' ); ?>
	</a>
	<a href="?page=younitedpay_settings&option=faq" class="nav-tab 
		<?php if ( 'faq' === $option ) : ?>
			nav-tab-active
		<?php endif; ?>
	">
		<?php echo esc_html__( 'Q/A', 'wc-younitedpay-gateway' ); ?>
	</a>
	<a href="?page=wc-status&tab=logs" class="nav-tab">
		<?php echo esc_html__( 'Logs', 'wc-younitedpay-gateway' ); ?>
	</a>
	<a href="?page=younitedpay_settings&option=support" class="nav-tab 
		<?php if ( 'support' === $option ) { ?>
			nav-tab-active
		<?php } ?>
	">
		<?php echo esc_html__( 'Support', 'wc-younitedpay-gateway' ); ?>
	</a>
</nav>
