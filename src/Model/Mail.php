<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\SimpleMail\Model;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Address;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\TransportInterfaceFactory;

/**
 * A simple mail with senders, receivers, copy and blind copy receivers, subject, body and additional headers, sent
 * with the mail transport of Magento.
 *
 * Addresses are kept as e-mail address => name (or null).
 */
class Mail
{
    /**
     * @var array
     * @phpstan-var array<string, string|null>
     */
    private array $additionalHeaders = [];

    /**
     * @var array
     * @phpstan-var array<string, string|null>
     */
    private array $senders = [];

    /**
     * @var array
     * @phpstan-var array<string, string|null>
     */
    private array $receivers = [];

    /**
     * @var array
     * @phpstan-var array<string, string|null>
     */
    private array $copyReceivers = [];

    /**
     * @var array
     * @phpstan-var array<string, string|null>
     */
    private array $blindCopyReceivers = [];

    /** @var string */
    private string $type = 'text/plain';

    /** @var string */
    private string $subject = '';

    /** @var string */
    private string $body = '';

    /**
     * @param MimePartInterfaceFactory $mimePartInterfaceFactory
     * @param MimeMessageInterfaceFactory $mimeMessageInterfaceFactory
     * @param EmailMessageFactory $emailMessageFactory
     * @param TransportInterfaceFactory $mailTransportFactory
     * @param AddressConverter $addressConverter
     */
    public function __construct(
        protected MimePartInterfaceFactory $mimePartInterfaceFactory,
        protected MimeMessageInterfaceFactory $mimeMessageInterfaceFactory,
        protected EmailMessageFactory $emailMessageFactory,
        protected TransportInterfaceFactory $mailTransportFactory,
        protected AddressConverter $addressConverter
    ) {
    }

    /**
     * Returns the additional headers as name => value.
     *
     * @return array
     * @phpstan-return array<string, string|null>
     */
    public function getAdditionalHeaders(): array
    {
        return $this->additionalHeaders;
    }

    /**
     * Sets the additional headers as name => value.
     *
     * @param array $additionalHeaders
     * @phpstan-param array<string, string|null> $additionalHeaders
     *
     * @return void
     */
    public function setAdditionalHeaders(array $additionalHeaders): void
    {
        $this->additionalHeaders = $additionalHeaders;
    }

    /**
     * Adds a header, for example "X-Sender".
     *
     * @param string $headerName
     * @param string|null $headerValue
     *
     * @return void
     */
    public function addAdditionalHeader(string $headerName, ?string $headerValue = null): void
    {
        $this->additionalHeaders[ $headerName ] = $headerValue;
    }

    /**
     * Returns the senders as e-mail address => name.
     *
     * @return array
     * @phpstan-return array<string, string|null>
     */
    public function getSenders(): array
    {
        return $this->senders;
    }

    /**
     * Sets the senders as e-mail address => name.
     *
     * @param array $senders
     * @phpstan-param array<string, string|null> $senders
     *
     * @return void
     */
    public function setSenders(array $senders): void
    {
        $this->senders = $senders;
    }

    /**
     * Adds a sender.
     *
     * @param string $senderEMail
     * @param string|null $senderName
     *
     * @return void
     */
    public function addSender(string $senderEMail, ?string $senderName = null): void
    {
        $this->senders[ $senderEMail ] = $senderName;
    }

    /**
     * Returns the receivers as e-mail address => name.
     *
     * @return array
     * @phpstan-return array<string, string|null>
     */
    public function getReceivers(): array
    {
        return $this->receivers;
    }

    /**
     * Sets the receivers as e-mail address => name.
     *
     * @param array $receivers
     * @phpstan-param array<string, string|null> $receivers
     *
     * @return void
     */
    public function setReceivers(array $receivers): void
    {
        $this->receivers = $receivers;
    }

    /**
     * Adds a receiver.
     *
     * @param string $receiverEMail
     * @param string|null $receiverName
     *
     * @return void
     */
    public function addReceiver(string $receiverEMail, ?string $receiverName = null): void
    {
        $this->receivers[ $receiverEMail ] = $receiverName;
    }

    /**
     * Returns the copy receivers (Cc) as e-mail address => name.
     *
     * @return array
     * @phpstan-return array<string, string|null>
     */
    public function getCopyReceivers(): array
    {
        return $this->copyReceivers;
    }

    /**
     * Sets the copy receivers (Cc) as e-mail address => name.
     *
     * @param array $copyReceivers
     * @phpstan-param array<string, string|null> $copyReceivers
     *
     * @return void
     */
    public function setCopyReceivers(array $copyReceivers): void
    {
        $this->copyReceivers = $copyReceivers;
    }

    /**
     * Adds a copy receiver (Cc).
     *
     * @param string $receiverEMail
     * @param string|null $receiverName
     *
     * @return void
     */
    public function addCopyReceiver(string $receiverEMail, ?string $receiverName = null): void
    {
        $this->copyReceivers[ $receiverEMail ] = $receiverName;
    }

    /**
     * Returns the blind copy receivers (Bcc) as e-mail address => name.
     *
     * @return array
     * @phpstan-return array<string, string|null>
     */
    public function getBlindCopyReceivers(): array
    {
        return $this->blindCopyReceivers;
    }

    /**
     * Sets the blind copy receivers (Bcc) as e-mail address => name.
     *
     * @param array $blindCopyReceivers
     * @phpstan-param array<string, string|null> $blindCopyReceivers
     *
     * @return void
     */
    public function setBlindCopyReceivers(array $blindCopyReceivers): void
    {
        $this->blindCopyReceivers = $blindCopyReceivers;
    }

    /**
     * Adds a blind copy receiver (Bcc).
     *
     * @param string $receiverEMail
     * @param string|null $receiverName
     *
     * @return void
     */
    public function addBlindCopyReceiver(string $receiverEMail, ?string $receiverName = null): void
    {
        $this->blindCopyReceivers[ $receiverEMail ] = $receiverName;
    }

    /**
     * Returns the MIME type of the body, "text/plain" or "text/html".
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Sets the MIME type of the body, "text/plain" or "text/html".
     *
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Returns the subject.
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Sets the subject. HTML entities are decoded when the mail is sent.
     *
     * @param string $subject
     *
     * @return void
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Returns the body.
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Sets the body.
     *
     * @param string $body
     *
     * @return void
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * Creates the message with the addresses, the subject, the body and the additional headers.
     *
     * @return EmailMessage
     */
    public function createMessage(): EmailMessage
    {
        $mimePart = $this->mimePartInterfaceFactory->create(
            ['content' => $this->getBody(), 'type' => $this->getType()]
        );

        $message = $this->emailMessageFactory->create(
            [
                'from'     => $this->convertAddresses($this->getSenders()),
                'to'       => $this->convertAddresses($this->getReceivers()),
                'cc'       => $this->convertAddresses($this->getCopyReceivers()),
                'bcc'      => $this->convertAddresses($this->getBlindCopyReceivers()),
                'encoding' => $mimePart->getCharset(),
                // as Magento's TransportBuilder: subjects of templates contain HTML entities
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                'subject'  => html_entity_decode($this->getSubject(), ENT_QUOTES),
                'body'     => $this->mimeMessageInterfaceFactory->create(['parts' => [$mimePart]])
            ]
        );

        foreach ($this->getAdditionalHeaders() as $headerName => $headerValue) {
            $message->addHeaderLine($headerName, $headerValue);
        }

        return $message;
    }

    /**
     * Sends the mail with the mail transport of Magento.
     *
     * @return void
     * @throws MailException
     */
    public function send(): void
    {
        $mailTransport = $this->mailTransportFactory->create(['message' => $this->createMessage()]);

        $mailTransport->sendMessage();
    }

    /**
     * Converts addresses as e-mail address => name into address objects.
     *
     * @param array $addresses
     * @phpstan-param array<string, string|null> $addresses
     *
     * @return Address[]
     */
    private function convertAddresses(array $addresses): array
    {
        $result = [];

        foreach ($addresses as $email => $name) {
            $result[] = $this->addressConverter->convert($email, $name);
        }

        return $result;
    }
}
