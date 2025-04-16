# PayOrc Payment Gateway for WordPress

## Description

PayOrc Payment Gateway for WordPress provides a simple and secure way to accept payments on your WordPress website. The plugin supports both iframe and hosted checkout modes, making it flexible for different use cases.

Signup for sandbox account: https://merchant.payorc.com/console/merchant-signup

Visit API documentation: https://api.payorc.com

## Features

- Easy integration with PayOrc payment gateway
- Support for both test and live modes
- Flexible checkout options (iframe or hosted checkout)
- Simple shortcode implementation

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- PayOrc merchant account

## Installation

1. Download the plugin zip file
2. Go to WordPress admin panel > Plugins > Add New
3. Click "Upload Plugin" and select the downloaded zip file
4. Click "Install Now" and then "Activate"
5. Go to PayOrc settings in the WordPress admin menu
6. Configure your PayOrc merchant credentials and settings

## Configuration

1. Navigate to PayOrc settings in the WordPress admin menu
2. Enter your PayOrc merchant credentials:
   - Test Mode: Enable/disable test mode
   - Test/Live Merchant Key: Your PayOrc merchant key
   - Test/Live Merchant Secret: Your PayOrc merchant secret
3. Configure checkout settings:
   - Checkout Mode: Choose between iframe or hosted checkout
   - Action Type: Select SALE or AUTH
   - Capture Method: Choose AUTOMATIC or MANUAL

## Usage

### Basic Payment Button

Add a payment button to any post or page using the shortcode:

```
[payorc_payment 
    amount="15.00" 
    currency="AED" 
    order_id="5482"
    description="Order 5482"
    customer_name="John Doe"
    customer_email="john@example.com"
    customer_phone="1234567890"
    customer_address_line1="123 Main St"
    customer_address_line2="Apt 4B"
    customer_city="New York"
    customer_province="NY"
    customer_pin="10001"
    customer_country="US"
]
```

### Shortcode Parameters

All parameters are optional except `amount`:

- `amount` (required): Payment amount (e.g., "15.00")
- `currency`: Payment currency (default: "USD")
- `order_id`: Unique order identifier
- `description`: Order description
- `button_text`: Custom button text (default: "Pay Now")
- `button_class`: Custom CSS classes for styling
- `success_url`: URL to redirect after successful payment
- `cancel_url`: URL to redirect after cancelled payment
- `failure_url`: URL to redirect after failed payment
- `customer_name`: Customer's full name
- `customer_email`: Customer's email address
- `customer_phone`: Customer's phone number
- `customer_address_line1`: Primary address line
- `customer_address_line2`: Secondary address line
- `customer_city`: City name
- `customer_province`: State/Province
- `customer_pin`: ZIP/Postal code
- `customer_country`: Two-letter country code (e.g., "US", "GB")

### Examples

1. Basic payment button with customer details:
```
[payorc_payment 
    amount="15.00" 
    currency="AED" 
    order_id="5482"
    description="Order 5482"
    customer_name="John Doe"
    customer_email="john@example.com"
    customer_phone="123456789"
    customer_address_line1="123 Main St"
    customer_city="Apt 4B"
    customer_province="NY"
    customer_pin="10001"
    customer_country="US"
]
```

2. Button with specific URLs:
```
[payorc_payment 
    amount="50.00" 
    currency="USD"
    button_text="Pay $50"
    button_class="my-custom-button"
    success_url="https://example.com/thank-you"
    cancel_url="https://example.com/cart"
    failure_url="https://example.com/payment-failed"
]
```


## Support

For issues or questions, please contact PayOrc support or visit the official documentation.

  

---

  

### Security Notice

- Keep your **Merchant Secret** confidential.
