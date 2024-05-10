<?php

declare(strict_types=1);

namespace Infrangible\SimpleMail\Controller\Adminhtml\Mail;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Controller\Adminhtml\Ajax;
use Infrangible\Core\Helper\Stores;
use Infrangible\SimpleMail\Model\MailFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Test
    extends Ajax
{
    /** @var Variables */
    protected $variables;

    /** @var Stores */
    protected $storeHelper;

    /** @var MailFactory */
    protected $mailFactory;

    public function __construct(
        Arrays $arrays,
        Json $json,
        Context $context,
        LoggerInterface $logging,
        Variables $variables,
        Stores $storeHelper,
        MailFactory $mailFactory
    ) {
        parent::__construct($arrays, $json, $context, $logging);

        $this->variables = $variables;
        $this->storeHelper = $storeHelper;
        $this->mailFactory = $mailFactory;
    }

    public function execute(): void
    {
        $senderIdentity = $this->_request->getParam('infrangible_simple_mail_test_mail_sender');
        $recipientEMail = $this->_request->getParam('infrangible_simple_mail_test_mail_receiver');

        $type = 'text/plain';
        $body = __('This is a test message.')->render();
        $subject = 'Test Mail';

        $senderEMail = $this->storeHelper->getStoreConfig(sprintf('trans_email/ident_%s/email', $senderIdentity));
        $senderName = $this->storeHelper->getStoreConfig(sprintf('trans_email/ident_%s/name', $senderIdentity));

        $mail = $this->mailFactory->create();

        $mail->addSender($senderEMail, $senderName);
        $mail->addReceiver($recipientEMail);
        $mail->setType($type);
        $mail->setSubject($subject);
        $mail->setBody($body);

        try {
            $mail->send();

            $this->setSuccessResponse(__('The message was successfully sent.')->render());
        } catch (MailException $exception) {
            $this->setErrorResponse($exception->getMessage());
        }
    }
}
