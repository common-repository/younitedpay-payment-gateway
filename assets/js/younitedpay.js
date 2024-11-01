jQuery(function ($) {


	$(document).ready(function () {


		const urlParams = new URLSearchParams(window.location.search);
		const paymentFailed = urlParams.get('younited-msg');
		if (paymentFailed) {
			var form = $('form[name="checkout"]');
			var text = paymentFailed;
			var html = '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout"><ul class="woocommerce-error" role="alert"><li>' + text + '</li></ul></div>';
			form.prepend(html);
		}

		// Choose maturity in checkout page
		$('body').on('click', 'li.payment_method_younitedpay-gateway fieldset ul li:not(.checked)', function () {
			$('li.payment_method_younitedpay-gateway fieldset ul li.checked').removeClass('checked');
			$(this).addClass('checked');
			$(this).find('input').prop('checked', true);
		});

		const displayCheckoutDetailsMaturity = function () {
			$('.younitedpay_checkout_details_maturity').css("display", "none");
			var maturity_selected = $('.younitedpay-checkout-details select').val();
			if (maturity_selected) {
				$('#younitedpay_checkout_details_maturity_' + maturity_selected).css("display", "block");
			}
		}

		if ($('#payment_method_younitedpay-gateway').length > 0) {
			//Si on recharge la page, on vérifie que la maturité n'est pas checké
			if ($('#payment_method_younitedpay-gateway').is(":checked")) {
				displayCheckoutDetailsMaturity();
			}

			$('body').on('change', '#payment_method_younitedpay-gateway', function () {
				if ($('#payment_method_younitedpay-gateway').is(":checked")) {
					displayCheckoutDetailsMaturity();
				}
			});

			$('body').on('change', '.younitedpay-checkout-details select', function () {
				displayCheckoutDetailsMaturity();
			});
		}
	});
});