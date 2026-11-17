<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\SimpleMail\Test\Unit\Controller\Adminhtml\Mail;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Infrangible\SimpleMail\Controller\Adminhtml\Mail\Test;
use Infrangible\SimpleMail\Model\EmailMessageFactory;
use Infrangible\SimpleMail\Model\Mail;
use Infrangible\SimpleMail\Model\MailFactory;
use Magento\Backend\App\Action\Context;
use Magento\Config\Model\Config\Source\Email\Identity;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\TransportInterfaceFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Tests the test mail of the configuration: the sender identity and the receiver are checked before the mail is sent.
 */
class TestTest extends TestCase
{
    /** @var Mail|null */
    private ?Mail $mail = null;

    /**
     * Returns the controller with the request parameters of the configuration form.
     *
     * @param string|null $sender
     * @param string|null $receiver
     * @param string|null $error
     *
     * @return Test
     */
    private function createController(?string $sender, ?string $receiver, ?string $error = null): Test
    {
        $request = $this->createStub(HttpRequest::class);
        $request->method('getParam')->willReturnMap(
            [
                ['infrangible_simple_mail_test_mail_sender', null, $sender],
                ['infrangible_simple_mail_test_mail_receiver', null, $receiver]
            ]
        );

        $context = $this->createStub(Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getResponse')->willReturn($this->createStub(HttpResponse::class));

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('getStoreConfig')->willReturnMap(
            [
                ['trans_email/ident_sales/email', null, false, null, 'sales@example.com'],
                ['trans_email/ident_sales/name', null, false, null, 'Sales']
            ]
        );

        $identitySource = $this->createStub(Identity::class);
        $identitySource->method('toOptionArray')->willReturn(
            [['value' => 'general', 'label' => 'General Contact'], ['value' => 'sales', 'label' => 'Sales']]
        );

        $mail = new SentMail(
            $this->createStub(MimePartInterfaceFactory::class),
            $this->createStub(MimeMessageInterfaceFactory::class),
            $this->createStub(EmailMessageFactory::class),
            $this->createStub(TransportInterfaceFactory::class),
            $this->createStub(AddressConverter::class)
        );
        $mail->setError($error);
        $this->mail = $mail;

        $mailFactory = $this->createStub(MailFactory::class);
        $mailFactory->method('create')->willReturn($mail);

        $variables = new Variables();

        return new Test(
            new Arrays($variables),
            new Json(),
            $context,
            new NullLogger(),
            $variables,
            $storeHelper,
            $mailFactory,
            $identitySource
        );
    }

    /**
     * Tests the mail that is sent.
     *
     * @return void
     */
    public function testSendsMail(): void
    {
        $controller = $this->createController('sales', ' receiver@example.com ');

        $controller->execute();

        $this->assertSame('The message was successfully sent.', $controller->getResponseValues()[ 'message' ] ?? null);

        $mail = $this->mail;

        $this->assertInstanceOf(Mail::class, $mail);
        $this->assertSame(['sales@example.com' => 'Sales'], $mail->getSenders());
        $this->assertSame(['receiver@example.com' => null], $mail->getReceivers());
        $this->assertSame('Test Mail', $mail->getSubject());
        $this->assertSame('This is a test message.', $mail->getBody());
        $this->assertSame(['X-Sender' => 'Test Mail'], $mail->getAdditionalHeaders());
    }

    /**
     * Tests that only the sender identities of the store are accepted.
     *
     * @return void
     */
    public function testUnknownSender(): void
    {
        $controller = $this->createController('../custom', 'receiver@example.com');

        $controller->execute();

        $this->assertSame('Please select a sender.', $controller->getResponseValues()[ 'message' ] ?? null);
        $this->assertSame([], $this->mail?->getReceivers());
    }

    /**
     * Tests that the receiver has to be an e-mail address.
     *
     * @return void
     */
    public function testInvalidReceiver(): void
    {
        $controller = $this->createController('sales', null);

        $controller->execute();

        $this->assertSame(
            'Please enter a valid e-mail address as receiver.',
            $controller->getResponseValues()[ 'message' ] ?? null
        );
    }

    /**
     * Tests that an error of the mail transport is reported.
     *
     * @return void
     */
    public function testMailError(): void
    {
        $controller = $this->createController('sales', 'receiver@example.com', 'Failed');

        $controller->execute();

        $this->assertSame('Failed', $controller->getResponseValues()[ 'message' ] ?? null);
    }
}
