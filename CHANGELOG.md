# Changelog

## 2.0.0

### Breaking changes

- Requires Magento 2.4.6 or newer and PHP 8.2 or newer. Magento 2.3 to 2.4.5 and PHP 7.1 to 8.1 are no longer
  supported; use version 1.x for these.
- Requires `infrangible/m2-core` 5.0, `infrangible/m2-foundation` 3.0, `fewedev/base` 2.4, `magento/module-backend`
  and `magento/module-config`.
- All methods of the mail have native return types, and the constructors use constructor property promotion. The
  controller of the test mail gets the sender identities of the store
  (`Magento\Config\Model\Config\Source\Email\Identity`).

### Added

- `Mail::createMessage()` returns the message without sending it.
- Translations into 24 locales.

### Fixed

- Additional headers work with Magento 2.4.8 and newer, which build the message with Symfony Mime instead of Laminas
  Mail. Before, the mail failed as soon as it had an additional header.
- Only admin users who may change the configuration of Simple Mail can send the test mail. Before, every admin user
  could.
- The test mail only accepts the sender identities of the store and a valid e-mail address as receiver, and reports
  missing input instead of failing.
- A mail without subject or body no longer fails.

### Changed

- The module is loaded after Infrangible_Core and Magento_Config.
- Checks with PHPStan level 9, the Magento coding standard and unit tests on every push.
