# FonePay WHMCS Payment Gateway Module #

## Summary ##

Payment Gateway modules allow you to integrate payment solutions with the WHMCS
platform.

* Merchant Gateways - these are payment solutions where credit card details
are collected - usually within the WHMCS application, though more and more
often this will be done remotely, typically via an iframe, with a page hosted
remotely by the payment gateway enabling tokenised storage.

For more information, please refer to the documentation at:
https://developers.whmcs.com/payment-gateways/

## Module Content ##

The recommended structure of a third party gateway module is as follows.

```
 modules/gateways/
  |- callback/fonepaygeteway.php
  |  fonepaygeteway.php
```

## Minimum Requirements ##

For the latest WHMCS minimum system requirements, please refer to
https://docs.whmcs.com/System_Requirements

We recommend your module follows the same minimum requirements wherever
possible.

## Useful Resources
* [Developer Resources](https://developers.whmcs.com/)
* [Hook Documentation](https://developers.whmcs.com/hooks/)
* [API Documentation](https://developers.whmcs.com/api/)

[WHMCS Limited](https://www.whmcs.com)
