<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\SimpleMail\Controller\Adminhtml\Mail;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Controller\Adminhtml\Ajax;
use Infrangible\Core\Helper\Stores;
use Infrangible\SimpleMail\Model\MailFactory;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\Config\Source\Email\Identity;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;

/**
 * Sends a test mail from a sender identity of the store to a receiver, both taken from the configuration form.
 */
class Test extends Ajax
{
    /** Allowed for users who may change the configuration of the module. */
    public const ADMIN_RESOURCE = 'Infrangible_SimpleMail::config_infrangible_simple_mail';

    /**
     * @param Arrays $arrays
     * @param Json $json
     * @param Context $context
     * @param LoggerInterface $logging
     * @param Variables $variables
     * @param Stores $storeHelper
     * @param MailFactory $mailFactory
     * @param Identity $identitySource
     */
    public function __construct(
        Arrays $arrays,
        Json $json,
        Context $context,
        LoggerInterface $logging,
        protected Variables $variables,
        protected Stores $storeHelper,
        protected MailFactory $mailFactory,
        protected Identity $identitySource
    ) {
        parent::__construct($arrays, $json, $context, $logging);
    }

    /**
     * Sends the test mail and answers with success or the error.
     *
     * Throws nothing; without the tag, PHPStan would take the NotFoundException of ActionInterface::execute().
     *
     * @return ResponseInterface
     * @throws void
     */
    public function execute(): ResponseInterface
    {
        $senderIdentity = $this->variables->stringValue(
            $this->getRequest()->getParam('infrangible_simple_mail_test_mail_sender')
        );
        $receiverEMail = trim(
            $this->variables->stringValue($this->getRequest()->getParam('infrangible_simple_mail_test_mail_receiver'))
        );

        if (! in_array($senderIdentity, $this->getSenderIdentities(), true)) {
            $this->setErrorResponse(__('Please select a sender.')->render());

            return $this->getResponse();
        }

        if (filter_var($receiverEMail, FILTER_VALIDATE_EMAIL) === false) {
            $this->setErrorResponse(__('Please enter a valid e-mail address as receiver.')->render());

            return $this->getResponse();
        }

        $senderEMail = $this->variables->stringValue(
            $this->storeHelper->getStoreConfig(sprintf('trans_email/ident_%s/email', $senderIdentity))
        );
        $senderName = $this->variables->stringValue(
            $this->storeHelper->getStoreConfig(sprintf('trans_email/ident_%s/name', $senderIdentity))
        );

        $mail = $this->mailFactory->create();

        $mail->addSender($senderEMail, $senderName === '' ? null : $senderName);
        $mail->addReceiver($receiverEMail);
        $mail->setType('text/plain');
        $mail->setSubject('Test Mail');
        $mail->setBody(__('This is a test message.')->render());
        $mail->addAdditionalHeader('X-Sender', 'Test Mail');

        try {
            $mail->send();

            $this->setSuccessResponse(__('The message was successfully sent.')->render());
        } catch (MailException $exception) {
            $this->logging->error($exception);
            $this->setErrorResponse($exception->getMessage());
        }

        return $this->getResponse();
    }

    /**
     * Returns the codes of the sender identities of the store, for example "general" or "sales".
     *
     * @return string[]
     */
    private function getSenderIdentities(): array
    {
        $identities = [];

        foreach ($this->identitySource->toOptionArray() as $option) {
            if (is_array($option) && is_string($option[ 'value' ] ?? null)) {
                $identities[] = $option[ 'value' ];
            }
        }

        return $identities;
    }
}
