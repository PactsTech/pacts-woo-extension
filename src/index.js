import { useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';

const settings = getSetting('pacts', {});
const defaultLabel = __('Pacts Payments', 'woo-gutenberg-products-block');
const label = decodeEntities(settings.title) || defaultLabel;

const Content = ({ billing, cartData, shippingData, eventRegistration, emitResponse }) => {
  const { onPaymentSetup } = eventRegistration;

  console.log({ billing, cartData, shippingData });

  // TODO use cartTotalItems
  // TODO get selected chain

  useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {
      const { CHECKOUT_STORE_KEY } = window.wc.wcBlocksData;
      const store = select(CHECKOUT_STORE_KEY);
      console.log({ store });
      const orderId = store.getOrderId();
      console.log({ orderId });
      const pactsData = window.wc.wcSettings.getSetting('pacts_data');
      console.log({ pactsData });
      return {
        type: emitResponse.responseTypes.ERROR,
        message: 'An error ocurred'
      };
    });
    return () => unsubscribe();
  }, [
    emitResponse.responseTypes.ERROR,
    emitResponse.responseTypes.SUCCESS,
    onPaymentSetup
  ]);

  return decodeEntities(settings.description || '');
};

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