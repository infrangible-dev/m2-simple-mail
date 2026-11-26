# Infrangible Simple Mail

Sends simple mails without e-mail templates, for example reports of tasks, with the mail transport of Magento. Mail
modules and SMTP settings of the store therefore apply to these mails as well. The configuration gets a button to send
a test mail.

## Sending mails

`Infrangible\SimpleMail\Model\Mail` collects the parts of a mail and sends it:

```php
$mail = $this->mailFactory->create(); // Infrangible\SimpleMail\Model\MailFactory

$mail->addSender('shop@example.com', 'Example Shop');
$mail->addReceiver('receiver@example.com');
$mail->addCopyReceiver('copy@example.com', 'Copy');
$mail->addBlindCopyReceiver('archive@example.com');
$mail->setSubject('Import finished');
$mail->setBody('<p>All products were imported.</p>');
$mail->setType('text/html'); // default: text/plain
$mail->addAdditionalHeader('X-Sender', 'Import');

$mail->send(); // throws Magento\Framework\Exception\MailException
```

- Addresses are kept as e-mail address => name; the name is optional.
- HTML entities in the subject are decoded, as Magento does for the subjects of e-mail templates.
- Additional headers are given as name and value, or as a complete line (`X-Sender: Import`) without value.
- `createMessage()` returns the message without sending it.

`Infrangible\SimpleMail\Model\EmailMessage` extends Magento's e-mail message with `addHeaderLine()`, for Laminas Mail
(Magento 2.4.6 and 2.4.7) and Symfony Mime (Magento 2.4.8 and newer).

## Test mail

Stores > Configuration > Infrangible Solutions > Simple Mail > Test Mail: choose a sender identity of the store, enter a
receiver and click **Send**. The page shows whether the mail was sent, or the error. Only admin users who may change the
configuration of Simple Mail (ACL resource `Infrangible_SimpleMail::config_infrangible_simple_mail`) can send it.

## Requirements

- Magento Open Source or Adobe Commerce 2.4.6 or newer
- PHP 8.1 or newer

## Installation

```bash
composer require infrangible/m2-simple-mail
bin/magento module:enable Infrangible_SimpleMail
bin/magento setup:upgrade
```

In production mode, also run `bin/magento setup:di:compile`.

## License

Infrangible Simple Mail is licensed under the MIT License - see the LICENSE file for details.
