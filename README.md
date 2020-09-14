# Katapult Magento Module

Katapult is a payment method that uses Katapult's JS plugin, as described here
- https://cdn.katapult.com/developer/plugin.html

Version for Magento 2.3.5

----

## Installation

1. Place the provided package file within the `app/code/Katapult/Payment` directory
2. From terminal run the setup upgrade command, `php bin/magento setup:upgrade`
3. Confirm the module has been installed using `php bin/magento module:status`
4. From the administration panel, in payment method configurations add the needed API Keys
5. Clean the cache from administration panel or by command `php bin/magento cache:flush`

----

If you encounter any issues during the installation or module use please review this installation guide
- https://devdocs.magento.com/extensions/install/

or reach out to us at - integrations@katapult.com
