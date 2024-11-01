jQuery(function ($) {


	$(document).ready(function () {

		const younited_main_id = "#wc-younitedpay-gateway-cc-form";
		const urlParams = new URLSearchParams(window.location.search);
		const paymentFailed = urlParams.get('younited-msg');
		if (paymentFailed) {
			var form = $('form[name="checkout"]');
			var text = paymentFailed;
			var html = '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"><ul class="woocommerce-error" role="alert"><li>' + text + '</li></ul></div>';
			form.prepend(html);
		}

		const displayCheckoutDetailsMaturity = function () {
			$(younited_main_id + ' .younitedpay_checkout_details_maturity').css("display", "none");
			var maturity_selected = $(younited_main_id + ' .younitedpay-checkout-details select').val();
			if (maturity_selected) {
				$(younited_main_id + ' #younitedpay_checkout_details_maturity_' + maturity_selected).css("display", "block");
			}
		}

		const displayCheckoutDetailsMaturityForShortCode = function(){
			var is_checkout_shortcode = $('#payment_method_younitedpay-gateway').length > 0;
			if (is_checkout_shortcode) {
				if ($('#payment_method_younitedpay-gateway').is(":checked")) {
					displayCheckoutDetailsMaturity();
				}
	
				$('body').on('change', '#payment_method_younitedpay-gateway', function () {
					if ($('#payment_method_younitedpay-gateway').is(":checked")) {
						displayCheckoutDetailsMaturity();
					}
				});
			}
		}

		const displayCheckoutDetailsMaturityForBlock = function(){
			if ($('#radio-control-wc-payment-method-options-younitedpay-gateway').is(":checked")) {
				displayCheckoutDetailsMaturity();
			}

			$('body').on('change', '#radio-control-wc-payment-method-options-younitedpay-gateway', function () {	
				if ($('#radio-control-wc-payment-method-options-younitedpay-gateway').is(":checked")) {
					displayCheckoutDetailsMaturity();
				}
			});

			const observer = new MutationObserver((mutations) => {
				mutations.forEach((mutation) => {
					mutation.addedNodes.forEach((node) => {
						// Vérifie si le nœud ajouté est l'élément recherché
						if (node.id === 'payment-method') {		
							if ($('#radio-control-wc-payment-method-options-younitedpay-gateway').is(":checked")) {
								displayCheckoutDetailsMaturity();
							}					
						}
					});
				});
			});
			observer.observe(document.body, { childList: true, subtree: true });
		}

		// Choose maturity in checkout page
		$('body').on('click', younited_main_id + ' ul li:not(.checked)', function () {
			$(younited_main_id + ' ul li.checked').removeClass('checked');
			$(this).addClass('checked');
			$(this).find('input').prop('checked', true);
		});

		displayCheckoutDetailsMaturityForShortCode();
		displayCheckoutDetailsMaturityForBlock();	

		$('body').on('change', younited_main_id + ' .younitedpay-checkout-details select', function () {
			displayCheckoutDetailsMaturity();
		});
	});
});