<?php

namespace Younitedpay\WcYounitedpayGateway;

/**
 * Class WcYounitedpayFaq
 */
class WcYounitedpayFaq {

	public $question;
	public $answer;

	public function __construct( $q, $a ) {
		$this->question = $q;
		$this->answer   = $a;
	}

	private static function get_list_fr() {
		$faq_array = array();

		// TODO Attente formulaire ticket via mantis
		/*
		$faq_array[] = new WcYounitedpayFaq(
			esc_html__("J’ai un problème technique, qui contacter ?", 'wc-younitedpay-gateway'),
			esc_html__("Si votre problème concerne le module WordPress en lui-meme, veuillez-vous réferez à
			XXX (à définir).<br>
			Si votre problème concerne la solution YounitedPay, vous avez la possibilité de créer un
			ticket de support via votre Back Office YounitedPay", 'wc-younitedpay-gateway')
		);*/

		$faq_array[] = new WcYounitedpayFaq(
			'Je n’arrive plus à accéder à mon compte Younited Pay. Que dois-je faire ?',
			"Vous pouvez accéder à votre compte marchand Younited Pay ici:
             <a href='https://portal.younited-pay.com/home' target='_blank'>https://portal.younited-pay.com/home</a> . <br/>
            Si vous avez perdu votre mot de passe, nous vous invitons à le réinitialiser en cliquant
            sur « mot de passe oublié ». <br>
            Si vous avez oublié l’adresse email permettant de vous connecter à votre espace
            marchand, merci de prendre contact avec votre Account Manager"
		);
		$faq_array[] = new WcYounitedpayFaq(
			'Comment suivre mes commandes et ma comptabilité YounitedPay ?',
			'Vos commandes apparaitront dans votre back office WordPress comme vos
            commandes habituelles. Elles seront aussi disponible dans votre back office
            YounitedPay. <br>
            Pour consulter les mouvements comptables spécifiquement associés à vos
            transactions Younited Pay, rendez-vous dans la rubrique « Paiements » de votre espace
            marchand. <br> Vous aurez ainsi accès à l’ensemble des transactions (octroyé, en attente de
            livraison, annulé et livré). Vous pourrez faire une recherche sur la période de votre choix,
            à l’aide de la référence de contrat, nom, prénom ou email de vos clients. <br>
            Vos factures mensuelles seront téléchargeables au format PDF, et éditées
            automatiquement au début du mois. <br> Elles récapitulent le total des ventes effectuées
            avec Younited Pay au cours du mois précédent, les frais Younited Pay correspondant
            ainsi que les transactions qui ont été rétractées dans le délai légal. <br>
            Les fichiers téléchargés sont disponibles au format .xlsx ou .csv.'
		);
		$faq_array[] = new WcYounitedpayFaq(
			'Comment effectuer un remboursement d’une commande faite avec Younited Pay ?',
			"Un remboursement total peut s’effectuer depuis votre back office WordPress ou depuis
            votre back office YounitedPay. <br> Les remboursements partiels ne s’effectuent uniquement
            sur le back office YounitedPay. <br>
            Pour les remboursements partiels, si l'échéancier de votre client n'est pas encore soldé,
            un remboursement partiel viendra en priorité réduire le montant de ses échéances
            restantes. <br> Si le montant que vous souhaitez rembourser est plus important que le \"reste
            à payer\" de votre client, le complément sera crédité sur son compte bancaire dans un
            délai de 3 à 5 jours (délai variable selon les banques). <br> 
            Une fois le remboursement effectué, votre client sera immédiatement informé par un
            email dans lequel il trouvera son échéancier de paiement mis à jour."
		);

		$available_ips          = array(
			'52.166.181.85',
			'52.166.142.223',
			'52.166.176.207',
			'52.166.182.189',
			'52.166.181.35',
			'52.166.139.57',
			'13.95.148.144',
			'20.103.186.208',
			'20.103.186.209',
			'20.103.186.210',
			'20.103.186.211',
			'13.94.190.118',
			'13.94.190.119',
		);
		$available_ips_response = "Certains modules de sécurité vont bloquer les tentatives de paiement si vous n'autoriser nos adresses IPs à contacter votre site. <br> Si vous avez un module de sécurité installé sur votre site, veillez bien a ce qu'il autorise les demandes entrantes pour les adresses suivantes : <br> ";
		foreach ( $available_ips as $available_ip ) {
			$available_ips_response .= " <br> $available_ip";
		}
		$faq_array[] = new WcYounitedpayFaq(
			esc_html__( 'Modules et sécurité WordPress', 'wc-younitedpay-gateway' ),
			$available_ips_response
		);

		return $faq_array;
	}

	private static function get_list_es() {
		$faq_array = array();

		$faq_array[] = new WcYounitedpayFaq(
			'¿Qué es YounitedPay?',
			'Younited Pay es una oferta de crédito de Younited. Con Younited Pay, tus clientes pueden pagar sus compras a plazos, con vencimientos que van de 10x a 84x. <br> Esta solución está disponible para cestas de la compra de entre 300 y 50.000 euros. <br> Se te abonará la totalidad del pedido y nosotros nos encargaremos de los pagos pendientes. <br> Nuestros ingresos se basan en los intereses de credito o una comisión por las transacciones que se realizan a través de nuestra solución de pago. <br> No hay costes adicionales. Para más información: https://www.younited-pay.com'
		);
		$faq_array[] = new WcYounitedpayFaq(
			'¿Cómo funciona YounitedPay?',
			'Una vez que el cliente haya completado su pedido en tu sitio web y haya seleccionado YounitedPay con la duración que más le convenga, será redirigido a nuestra plataforma. <br> Durante este proceso, se le pedirá que verifique su identidad, conecte su cuenta bancaria y responda a algunas preguntas. <br> Estos pasos permiten que la solución YounitedPay esté estrictamente regulada y ofrezca un crédito responsable, de acuerdo con la legislación.'
		);
		$faq_array[] = new WcYounitedpayFaq(
			'¿Cómo muestro YounitedPay en mi página de producto?',
			'Para optimizar tu tasa de conversión y asegurarse de que tus clientes ven la oferta de YounitedPay, puedes mostrar las ofertas elegibles en tu página de producto. <br> Para ello, activa la función "Mostrar mensualidades" en la sección "Visualización" del módulo. <br> Si la visualización no es óptima, asegúrate de haber descargado la última versión del módulo.'
		);
		$faq_array[] = new WcYounitedpayFaq(
			'¿Cómo se gestionan las devoluciones?',
			'Hay dos opciones de devolución: completa y parcial. <br> Esta gestión se puede realizar directamente desde el back office de tu módulo Prestashop. <br> Simplemente necesitas cambiar el estado del pedido a Cancelar o Reembolsar.' .
				' <br> ' .
				'También se puede realizar una devolución parcial desde el pedido con el botón "Reembolso parcial" y la opción "Reembolso a YounitedPay" seleccionada.'
		);
		$faq_array[] = new WcYounitedpayFaq(
			'¿Cómo puedo probar el módulo si no dispongo de un entorno de prueba para mi web?',
			'Tienes la posibilidad de crear una Whitelist de direcciones IP, lo que permite probar el módulo en tu entorno de producción sin mostrarlo a todos los clientes.'
		);
		$faq_array[] = new WcYounitedpayFaq(
			'¿Qué información debo añadir a mis Condiciones Generales de Contratación?',
			'Para cumplir con la legislación, por favor, añade a tusTérminos y Condiciones: <br> ' .
				'• "[el Vendedor] ofrece a sus clientes el servicio de crédito de Younited para el pago de sus compras y la ejecución de su pago. Esto está condicionado a que el cliente acepte el acuerdo de crédito ofrecido por Younited." <br> ' .
				'• "Cualquier rechazo por parte de Younited de otorgar crédito para un pedido puede resultar en la cancelación del pedido." <br> ' .
				'• "Cualquier terminación de los Términos y Condiciones Generales que vinculan al cliente y [el Vendedor] resultará en la terminación del acuerdo de crédito entre Younited y el cliente." <br> ' .
				'• “El importe es pagado a través de un crédito aprobado por Younited S.A., sucursal en España de Younited S.A., con nombre comercial Younited credit y Younited, con sede legal y dirección general en España en Carrer de la Caravel·la la Niña, 12, 08017 Barcelona, con Número de Identificación Fiscal W2500913E, asociada a ASNEF n°A810, registrada en el Registro Mercantil de Barcelona con Tomo 45784, Folio 71, Hoja B-498886, Inscripción 1ª y autorizada por el Banco de España con número 1560, y en el Registro de Intermediarios de Seguro con número 11061268 (www.orias.fr)." <br> '
		);
		return $faq_array;
	}

	public static function get_list() {
		$current_lang_explode = explode( '_', get_locale() );
		return ( 'es' == $current_lang_explode[0] ) ? self::get_list_es() : self::get_list_fr();
	}
}
