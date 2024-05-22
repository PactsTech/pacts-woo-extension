import { useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { select } from '@wordpress/data';
import { registerPaymentMethod } from '@woocommerce/blocks-registry';
import { getSetting } from '@woocommerce/settings';
import * as viemChains from 'viem/chains';
import { createPublicClient, createWalletClient, custom, publicActions } from 'viem';
import { getProcessor, setupOrder, submitOrder } from '@pactstech/pacts-viem';
import { ChainSelector, PactsRow, defineCustomElements } from '@pactstech/react-components';

defineCustomElements();

const { CHECKOUT_STORE_KEY, CART_STORE_KEY } = window.wc.wcBlocksData;
const settings = getSetting('pacts_data', {});
const { token, addresses, supports } = settings;
const chainNames = Object.keys(addresses).map((key) => key.replace('Address', ''));

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
      const now = new Date().getTime();
      const orderId = `${checkoutStore.getOrderId()}-${now}`;
      const { items, totals } = cartStore.getCartData();
      const chainId = await publicClient.getChainId();
      const [chainName, chain] = Object.entries(viemChains).find(([_, chain]) => chain.id === chainId);
      const address = addresses[chainName];
      const processor = getProcessor({ chain, address, client: walletClient });
      const price = Number(totals.total_price) / 100;
      const shipping = Number(totals.total_shipping) / 100;
      const metadata = items?.map?.((item) => ({
        name: item.name,
        quantity: item.quantity
      })) || [];
      const args = await setupOrder({
        chain,
        publicClient,
        walletClient,
        processor,
        orderId,
        price,
        shipping,
        metadata
      });
      const hash = await submitOrder({ chain, processor, ...args });
      const receipt = await publicClient.waitForTransactionReceipt({ chain, hash });
      if (receipt.status !== 'success') {
        return {
          type: emitResponse.responseTypes.ERROR,
          message: 'Your transaction failed'
        };
      }
      return {
        type: emitResponse.responseTypes.SUCCESS,
        meta: {
          paymentMethodData: { chain: chainName, hash, id: orderId }
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
    <>
      <span>Choose a Chain</span>
      <ChainSelector chains={chainNames.join(',')} iconSize='4rem' />
    </>
  );
};

const Label = () => (
  <div style={{ flex: '1' }}>
    <PactsRow token={token} />
  </div>
);

registerPaymentMethod({
  name: 'pacts',
  label: <Label />,
  content: <Content />,
  edit: <Content />,
  canMakePayment: () => !!token && token !== 'none',
  ariaLabel: 'Pacts Payment Method',
  supports: { features: supports }
});