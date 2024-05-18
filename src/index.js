import { useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { decodeEntities } from '@wordpress/html-entities';
import { getSetting } from '@woocommerce/settings';
import * as viemChains from 'viem/chains';
import { createPublicClient, createWalletClient, custom, publicActions } from 'viem';
import { getProcessor, setupOrder, submitOrder } from '@pactstech/pacts-viem';

const { CHECKOUT_STORE_KEY, CART_STORE_KEY } = window.wc.wcBlocksData;
const settings = getSetting('pacts_data', {});
const defaultLabel = __('Pacts Payments', 'woo-gutenberg-products-block');
const { title, addresses, supports } = settings;
const label = decodeEntities(title) || defaultLabel;
const chainNames = Object.keys(addresses).map((key) => key.replace('Address', ''));
const chains = chainNames.map((chainName) => viemChains[chainName]);

const transport = custom(window.ethereum);
const publicClient = createPublicClient({ transport });
const walletClient = createWalletClient({ transport }).extend(publicActions);

const Content = ({ eventRegistration, emitResponse }) => {
  const { onPaymentSetup } = eventRegistration;

  const onChainSelected = async (id) => walletClient.switchChain({ id });

  useEffect(() => {
    const unsubscribe = onPaymentSetup(async () => {
      const checkoutStore = select(CHECKOUT_STORE_KEY);
      const cartStore = select(CART_STORE_KEY);
      const orderId = checkoutStore.getOrderId();
      const cartData = cartStore.getCartData();
      const chainId = await publicClient.getChainId();
      const tuple = Object.entries(viemChains).find(([_, chain]) => chain.id === chainId);
      const chainName = tuple?.[0];
      const address = addresses[chainName];
      const processor = getProcessor({ address, client: walletClient });
      const price = BigInt(cartData.total_price);
      const shipping = BigInt(cartData.total_shipping);
      const metadata = cartData.items?.map?.((item) => ({
        name: item.name,
        quantity: item.quantity
      })) || [];
      const args = await setupOrder({
        publicClient,
        walletClient,
        processor,
        orderId,
        price,
        shipping,
        metadata
      });
      const hash = await submitOrder({ processor, ...args });
      const receipt = await publicClient.waitForTransactionReceipt({ hash });
      if (receipt.status !== 'success') {
        return {
          type: emitResponse.responseTypes.ERROR,
          message: 'Your transaction failed'
        };
      }
      return {
        type: emitResponse.responseTypes.SUCCESS,
        meta: {
          paymentMethodData: { transactionHash: hash }
        }
      }
    });
    return () => unsubscribe();
  }, [
    emitResponse.responseTypes.ERROR,
    emitResponse.responseTypes.SUCCESS,
    onPaymentSetup
  ]);

  return (
    <div>
      <p>Choose a Chain</p>
      {chains.map((chain) => (
        <span key={chain.id} onClick={() => onChainSelected(chain.id)}>
          {chain.name}
        </span>
      ))}
    </div>
  );
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
  supports: { features: supports }
});