<style>
	.accordion {
		margin-top: 10px;
		font-size: 14px;
	}

	.accordion-single {
		margin: 0px 0px 10px 0px;
	}

	.accordion-title span {
		float: right;
	}

	.acc-close .accordion-content {
		height: 0px;
		transition: transform 0.4s ease;
		transform: scaleY(0);
		display: block;
	}

	.acc-open .accordion-content {
		padding: 20px;
		background-color: #f0f1f1;
		border: 1px solid #ddd;
		width: 100%;
		margin: 0px 0px 10px 0px;
		display: block;
		transform: scaleY(1);
		transform-origin: top;
		transition: transform 0.4s ease;
		box-sizing: border-box;
	}

	.acc-open .accordion-title,
	.accordion-title {
		margin: 0px;
		border-top-left-radius: 3px;
		border-top-right-radius: 3px;
		border-bottom-right-radius: 0px;
		border-bottom-left-radius: 0px;
		background-color: #ddd;
		border: 1px solid #ddd;
		padding: 5px 20px;
	}
</style>

<div class="wrap">
	<h1><?php echo esc_html__( 'YounitedPay Payment Gateway', 'wc-younitedpay-gateway' ); ?></h1>

	<div class="wrap">
		<?php require __DIR__ . '/menu.php'; ?>

		<div class="accordion"><?php $i = 1; ?>
			<?php foreach ( $faq_array as $faq ) { ?>
				<div class="accordion-single acc-close">
					<div class="accordion-title"><?php echo esc_html( $faq->question ); ?><span class="plus">+</span></div>
					<div class="accordion-content">
						<?php
						echo wp_kses(
							$faq->answer,
							array(
								'br' => array(),
								'a'  => array(
									'href'   => array(),
									'target' => array(),
								),
							)
						);
						?>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>

<script>
	function younitedpayFaqToggleItem() {
		var itemClass = this.parentNode.className;
		for (i = 0; i < accSingle.length; i++) {
			accSingle[i].className = 'accordion-single acc-close';
		}
		if (itemClass == 'accordion-single acc-close') {
			this.parentNode.className = 'accordion-single acc-open';
		}
	}

	var accSingle = document.getElementsByClassName('accordion-single');
	var accTitle = document.getElementsByClassName('accordion-title');

	for (i = 0; i < accTitle.length; i++) {
		accTitle[i].addEventListener('click', younitedpayFaqToggleItem, false);
	}
</script>
