(function () {
	const settings = window.wc.wcSettings.getSetting('younitedpay-gateway_data', {});
	const label = window.wp.htmlEntities.decodeEntities(settings.title) || window.wp.i18n.__('Younited Pay', 'wc-younitedpay-gateway');

	let onPaymentSetupLoaded = false;

	const Content = props => {
		const { eventRegistration, emitResponse } = props;
		const { onPaymentSetup } = eventRegistration;

		if( !onPaymentSetupLoaded){
			onPaymentSetup(async () => {

				const younitedFormId = "#payment-method #wc-younitedpay-gateway-cc-form";
				const younitedPayMaturityInputChecked = document.querySelector( younitedFormId + ' input[name="maturity"]:checked');
				const younitedPayMaturitySelect = document.querySelector( younitedFormId + ' select[name="maturity"]');
				let younitedPayMaturityIsValid = false;
				let younitedPayMaturityValue = "";
				
				if( younitedPayMaturityInputChecked || younitedPayMaturitySelect){
					if(younitedPayMaturityInputChecked){
						younitedPayMaturityValue = younitedPayMaturityInputChecked.value;
					}else if(younitedPayMaturitySelect){
						younitedPayMaturityValue = younitedPayMaturitySelect.value;
					}
	
					younitedPayMaturityIsValid = !!younitedPayMaturityValue.length;
				}

				if (younitedPayMaturityIsValid) {
					return {
						type: emitResponse.responseTypes.SUCCESS,
						meta: {
							paymentMethodData: {
								'wc-younitedpay-gateway-blocks' : true,
								'wc-younitedpay-gateway-maturity' : younitedPayMaturityValue,
							},
						},
					};
				}
	
				return {
					type: emitResponse.responseTypes.ERROR,
					message: 'Maturity Error', 
				};
			});
			onPaymentSetupLoaded = true;
		}
		return Object(window.wp.element.createElement)('div', { dangerouslySetInnerHTML: { __html: settings.options } });
	};

	const Block_Gateway = {
		paymentMethodId: 'younitedpay-gateway',
		name: 'younitedpay-gateway',
		label: label,
		ariaLabel: label,
		content: Object(window.wp.element.createElement)(Content, null),
		edit: Object(window.wp.element.createElement)(Content, null),
		canMakePayment: () => true,
		supports: {
			features: settings.supports
		}
	};

	window.wc.wcBlocksRegistry.registerPaymentMethod(Block_Gateway);
})();
