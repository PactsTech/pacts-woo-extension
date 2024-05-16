import { __ } from '@wordpress/i18n';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting('pacts', {});
const defaultLabel = __('Pacts Payments', 'woo-gutenberg-products-block');
const label = decodeEntities(settings.title) || defaultLabel;

const Content = () => decodeEntities(settings.description || '');

const Label = ({ components }) => {
	const { PaymentMethodLabel } = components;
	return <PaymentMethodLabel text={label} />;
}

registerPaymentMethod({
	name: 'pacts',
	label: <Label />,
	content: <Content />,
	edit: <Content />,
	canMakePayment: () => true,
	ariaLabel: label,
	supports: { features: settings.supports }
});