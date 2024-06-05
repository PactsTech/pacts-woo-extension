# Pacts Woo Extension

A Woocommerce extension for [Pacts](https://pacts.tech) payment processing. This extension adds a Pacts payment gateway to your checkout page.

### Installation

1. Download **Plugin Zip** from the [latest release](https://github.com/PactsTech/pacts-woo-extension/releases/latest)
2. Navigate to **Add New Plugin** on your woocommerce admin page
3. Click **Upload Plugin**
4. Drop in the downloaded zip file

### Configuration

You must have a deployed processor in order to configure the plugin. To deploy a contract consult the [Getting Started](https://docs.pacts.tech/docs/getting-started) documentation.

1. In your admin page navigate to Woocommerce > Settings > Payments
2. Enable **Pacts** and click **Finish Setup**
3. Select the token you deployed your contract with
4. Input your order processor contract address for the correct network

## Development

### Prerequisites

-   [NPM](https://www.npmjs.com/)
-   [Composer](https://getcomposer.org/download/)
-   [wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)

### Installation and Build

```
npm install
npm run build
wp-env start
```

Visit the added page at http://localhost:8888/wp-admin/admin.php?page=wc-admin&path=%2Fexample.
