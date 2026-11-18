<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\SimpleMail\Test\Unit\Model;

use Infrangible\SimpleMail\Model\EmailMessage;
use Infrangible\SimpleMail\Model\EmailMessageFactory;
use Infrangible\SimpleMail\Model\Mail;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Address;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\MimeMessageInterface;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterface;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Mail\TransportInterfaceFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests that the mail creates the message from its addresses, subject, body and headers, and sends it.
 */
class MailTest extends TestCase
{
    /**
     * @var array
     * @phpstan-var array<string, mixed>
     */
    private array $messageData = [];

    /**
     * @var array
     * @phpstan-var array<string, mixed>
     */
    private array $mimePartData = [];

    /**
     * @var array
     * @phpstan-var array<int, array{0: string, 1: string|null}>
     */
    private array $headers = [];

    /**
     * @var array
     * @phpstan-var array<string, mixed>
     */
    private array $transportData = [];

    /** @var int */
    private int $sentMessages = 0;

    /** @var EmailMessage */
    private EmailMessage $message;

    /**
     * Creates the message, which records the headers that are added.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $message = $this->createStub(EmailMessage::class);
        $message->method('addHeaderLine')->willReturnCallback(
            function (string $name, ?string $value = null): void {
                $this->headers[] = [$name, $value];
            }
        );
        $this->message = $message;
    }

    /**
     * Returns a mail whose factories record what they get.
     *
     * @return Mail
     */
    private function createMail(): Mail
    {
        $mimePart = $this->createStub(MimePartInterface::class);
        $mimePart->method('getCharset')->willReturn('utf-8');

        $mimePartFactory = $this->createStub(MimePartInterfaceFactory::class);
        $mimePartFactory->method('create')->willReturnCallback(
            function (array $data) use ($mimePart): MimePartInterface {
                $this->mimePartData = $data;

                return $mimePart;
            }
        );

        $mimeMessageFactory = $this->createStub(MimeMessageInterfaceFactory::class);
        $mimeMessageFactory->method('create')->willReturn($this->createStub(MimeMessageInterface::class));

        $message = $this->message;

        $messageFactory = $this->createStub(EmailMessageFactory::class);
        $messageFactory->method('create')->willReturnCallback(
            function (array $data) use ($message): EmailMessage {
                $this->messageData = $data;

                return $message;
            }
        );

        $transport = $this->createStub(TransportInterface::class);
        $transport->method('sendMessage')->willReturnCallback(
            function (): void {
                $this->sentMessages++;
            }
        );

        $transportFactory = $this->createStub(TransportInterfaceFactory::class);
        $transportFactory->method('create')->willReturnCallback(
            function (array $data) use ($transport): TransportInterface {
                $this->transportData = $data;

                return $transport;
            }
        );

        $addressConverter = $this->createStub(AddressConverter::class);
        $addressConverter->method('convert')->willReturnCallback(
            fn(string $email, ?string $name = null): Address => new Address($email, $name)
        );

        return new Mail($mimePartFactory, $mimeMessageFactory, $messageFactory, $transportFactory, $addressConverter);
    }

    /**
     * Returns the e-mail addresses and names of addresses.
     *
     * @param mixed $addresses
     *
     * @return array
     * @phpstan-return array<string, string|null>
     */
    private function getAddresses(mixed $addresses): array
    {
        $result = [];

        foreach (is_array($addresses) ? $addresses : [] as $address) {
            if ($address instanceof Address) {
                $result[ (string)$address->getEmail() ] = $address->getName();
            }
        }

        return $result;
    }

    /**
     * Tests the message data: addresses, decoded subject, body and type.
     *
     * @return void
     */
    public function testCreateMessage(): void
    {
        $mail = $this->createMail();

        $mail->addSender('sender@example.com', 'Sender');
        $mail->addReceiver('first@example.com');
        $mail->addReceiver('second@example.com', 'Second');
        $mail->addCopyReceiver('copy@example.com');
        $mail->addBlindCopyReceiver('blind@example.com', 'Blind');
        $mail->setSubject('Fish &amp; Chips');
        $mail->setBody('<p>Body</p>');
        $mail->setType('text/html');

        $this->assertSame($this->message, $mail->createMessage());

        $this->assertSame(['content' => '<p>Body</p>', 'type' => 'text/html'], $this->mimePartData);
        $this->assertSame(
            ['sender@example.com' => 'Sender'],
            $this->getAddresses($this->messageData[ 'from' ] ?? null)
        );
        $this->assertSame(
            ['first@example.com' => null, 'second@example.com' => 'Second'],
            $this->getAddresses($this->messageData[ 'to' ] ?? null)
        );
        $this->assertSame(['copy@example.com' => null], $this->getAddresses($this->messageData[ 'cc' ] ?? null));
        $this->assertSame(['blind@example.com' => 'Blind'], $this->getAddresses($this->messageData[ 'bcc' ] ?? null));
        $this->assertSame('Fish & Chips', $this->messageData[ 'subject' ] ?? null);
        $this->assertSame('utf-8', $this->messageData[ 'encoding' ] ?? null);
    }

    /**
     * Tests that the additional headers are added to the message.
     *
     * @return void
     */
    public function testAdditionalHeaders(): void
    {
        $mail = $this->createMail();

        $mail->addAdditionalHeader('X-Sender', 'Test Mail');
        $mail->addAdditionalHeader('X-Mailer: Infrangible');
        $mail->createMessage();

        $this->assertSame([['X-Sender', 'Test Mail'], ['X-Mailer: Infrangible', null]], $this->headers);
    }

    /**
     * Tests that the message is sent with the mail transport.
     *
     * @return void
     * @throws MailException
     */
    public function testSend(): void
    {
        $mail = $this->createMail();

        $mail->addReceiver('receiver@example.com');
        $mail->send();

        $this->assertSame(['message' => $this->message], $this->transportData);
        $this->assertSame(1, $this->sentMessages);
    }

    /**
     * Tests the defaults of a new mail.
     *
     * @return void
     */
    public function testDefaults(): void
    {
        $mail = $this->createMail();

        $this->assertSame('text/plain', $mail->getType());
        $this->assertSame('', $mail->getSubject());
        $this->assertSame('', $mail->getBody());
        $this->assertSame([], $mail->getReceivers());
    }
}
