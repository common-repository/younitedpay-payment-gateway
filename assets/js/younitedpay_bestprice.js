jQuery(function ($) {
	$(document).ready(function () {

		var bestprice_action_list = $(".younitedpay-bestprice-action");
		var bestprice_action_to_delete = bestprice_action_list.not(function (index) { return index === 0; });
		bestprice_action_to_delete.remove();

		var bestprice_modal_list = $(".younitedpay-bestprice-modal");
		var bestprice_modal_to_delete = bestprice_modal_list.not(function (index) { return index === 0; });
		bestprice_modal_to_delete.remove();

		// Close popup
		$('body').on('click', '#younitedpay-bestprice-modal .younitedpay-bestprice-close', function () {
			$("#younitedpay-bestprice-modal").css("display", "none");
		});

		$(document).on('click', function (event) {
			var modal = $('#younitedpay-bestprice-modal');
			if (modal.css('display') === 'flex' && !modal.is(event.target) && modal.has(event.target).length === 0) {
				$("#younitedpay-bestprice-modal").css("display", "none");
			}
		});

		//Open modal
		$('body').on('click', '#younitedpay-bestprice-action .younitedpay-bestprice-action-button', function () {
			setTimeout(
				function () {
					$("#younitedpay-bestprice-modal").css("display", "flex");
				},
				200
			);

			var maturity = $('body').find('.younitedpay-default-maturity').html();

			var choices = $('#younitedpay-bestprice-modal .younitedpay-bestprice-month-choice');

			var maturity_default = choices.find('div[data-maturity-id="' + maturity + '"]');
			maturity_default.click();
		});

		//event click on maturity li in modal
		$('body').on('click', '#younitedpay-bestprice-modal .younitedpay-bestprice-month-choice > div', function () {
			$('#younitedpay-bestprice-modal .younitedpay-bestprice-month-choice > div').removeClass('younitedpay-bestprice-month-checked');
			$(this).addClass('younitedpay-bestprice-month-checked');

			var maturity = $(this).data("maturity-id");

			//titre
			$(".younitedpay-bestprice-price-per-month").css("display", "none");
			$("#younitedpay-maturity-info-" + maturity).css("display", "inline-flex");

			//bloc infos
			$('.younitedpay-bestprice-bloc').css("display", "none");
			$("#younitedpay-maturity_bloc_" + maturity).css("display", "block");

		});

		$('body').on('click', '#modal-bestprice-faq', function () {
			window.open("https://www.younited-credit.com/questions-reponses", "_blank")
		});

		const getPriceByVariation = function () {
			var variation_data = $('.variations_form').data('product_variations');
			// Récupère tous les éléments de sélection de variations
			var selectors = $('.variations_form select');
			
			if( !selectors || selectors.length == 0){
				return;
			}
			// Initialise la chaîne d'ID de variations sélectionnées
			var selected_ids = '';

			// Boucle à travers les sélecteurs pour construire la chaîne d'ID
			selectors.each(function () {
				selected_ids += $(this).val() + '-';
			});

			// Supprime le dernier tiret de la chaîne
			selected_ids = selected_ids.slice(0, -1);

			// Récupère le prix correspondant à la combinaison de variations sélectionnées
			var price = 0;
			for (var i = 0; i < variation_data.length; i++) {
				var variation = variation_data[i];
				var variation_combination = "";
				for (let var_att in variation.attributes) {
					variation_combination += variation.attributes[var_att] + '-';
				}
				// Supprime le dernier tiret de la chaîne
				variation_combination = variation_combination.slice(0, -1);
				if (variation_combination == selected_ids) {
					price = variation.display_price;
					break;
				}
			}

			if (price) {
				wp.ajax.send('fetch_shortcode_younitedpay', {
					data: {
						without_container: true,
						price: price,
						action: 'fetch_shortcode_younitedpay',
						fetch_shortcode_younitedpay_nonce: $('#fetch_shortcode_younitedpay_nonce').val()
					},
					success: function (response) {
						if (response) {
							$('#younitedpay-bestprice-container').html("");
							$('#younitedpay-bestprice-modal').remove();
							$('#younitedpay-bestprice-container').html(response);
							document.body.appendChild(document.getElementById('modal-bestprice'));
						}
					},
					error: function (error) {
						console.log(error);
					}
				});
			}
		};

		$('.variations_form').on('change', 'select', function () {
			getPriceByVariation();
		});

		//Get Price for default variations of product
		getPriceByVariation();
	});
});