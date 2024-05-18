<?php

declare(strict_types=1);

namespace Infrangible\SimpleMail\Model;

use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\AddressConverter;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePartInterfaceFactory;
use Magento\Framework\Mail\TransportInterfaceFactory;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Mail
{
    /** @var MimePartInterfaceFactory */
    protected $mimePartInterfaceFactory;

    /** @var MimeMessageInterfaceFactory */
    protected $mimeMessageInterfaceFactory;

    /** @var EmailMessageFactory */
    protected $emailMessageFactory;

    /** @var TransportInterfaceFactory */
    protected $mailTransportFactory;

    /** @var AddressConverter */
    protected $addressConverter;

    /** @var array */
    private $additionalHeaders = [];

    /** @var array */
    private $senders = [];

    /** @var array */
    private $receivers = [];

    /** @var array */
    private $copyReceivers = [];

    /** @var array */
    private $blindCopyReceivers = [];

    /** @var string */
    private $type = 'text/plain';

    /** @var string */
    private $subject;

    /** @var string */
    private $body;

    /**
     * @param MimePartInterfaceFactory    $mimePartInterfaceFactory
     * @param MimeMessageInterfaceFactory $mimeMessageInterfaceFactory
     * @param EmailMessageFactory         $emailMessageFactory
     * @param TransportInterfaceFactory   $mailTransportFactory
     * @param AddressConverter            $addressConverter
     */
    public function __construct(
        MimePartInterfaceFactory $mimePartInterfaceFactory,
        MimeMessageInterfaceFactory $mimeMessageInterfaceFactory,
        EmailMessageFactory $emailMessageFactory,
        TransportInterfaceFactory $mailTransportFactory,
        AddressConverter $addressConverter
    ) {
        $this->mimePartInterfaceFactory = $mimePartInterfaceFactory;
        $this->mimeMessageInterfaceFactory = $mimeMessageInterfaceFactory;
        $this->emailMessageFactory = $emailMessageFactory;
        $this->mailTransportFactory = $mailTransportFactory;
        $this->addressConverter = $addressConverter;
    }

    /**
     * @return array
     */
    public function getAdditionalHeaders(): array
    {
        return $this->additionalHeaders;
    }

    /**
     * @param array $additionalHeaders
     */
    public function setAdditionalHeaders(array $additionalHeaders): void
    {
        $this->additionalHeaders = $additionalHeaders;
    }

    /**
     * @param string      $headerName
     * @param string|null $headerValue
     */
    public function addAdditionalHeader(string $headerName, ?string $headerValue = null)
    {
        $this->additionalHeaders[$headerName] = $headerValue;
    }

    /**
     * @return array
     */
    public function getSenders(): array
    {
        return $this->senders;
    }

    /**
     * @param array $senders
     */
    public function setSenders(array $senders)
    {
        $this->senders = $senders;
    }

    /**
     * @param string      $senderEMail
     * @param string|null $senderName
     */
    public function addSender(string $senderEMail, ?string $senderName = null)
    {
        $this->senders[$senderEMail] = $senderName;
    }

    /**
     * @return array
     */
    public function getReceivers(): array
    {
        return $this->receivers;
    }

    /**
     * @param array $receivers
     */
    public function setReceivers(array $receivers)
    {
        $this->receivers = $receivers;
    }

    /**
     * @param string      $receiverEMail
     * @param string|null $receiverName
     */
    public function addReceiver(string $receiverEMail, ?string $receiverName = null)
    {
        $this->receivers[$receiverEMail] = $receiverName;
    }

    /**
     * @return array
     */
    public function getCopyReceivers(): array
    {
        return $this->copyReceivers;
    }

    /**
     * @param array $copyReceivers
     */
    public function setCopyReceivers(array $copyReceivers)
    {
        $this->copyReceivers = $copyReceivers;
    }

    /**
     * @param string      $receiverEMail
     * @param string|null $receiverName
     */
    public function addCopyReceiver(string $receiverEMail, ?string $receiverName = null)
    {
        $this->copyReceivers[$receiverEMail] = $receiverName;
    }

    /**
     * @return array
     */
    public function getBlindCopyReceivers(): array
    {
        return $this->blindCopyReceivers;
    }

    /**
     * @param array $blindCopyReceivers
     */
    public function setBlindCopyReceivers(array $blindCopyReceivers)
    {
        $this->blindCopyReceivers = $blindCopyReceivers;
    }

    /**
     * @param string      $receiverEMail
     * @param string|null $receiverName
     */
    public function addBlindCopyReceiver(string $receiverEMail, ?string $receiverName = null)
    {
        $this->blindCopyReceivers[$receiverEMail] = $receiverName;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }

    /**
     * @throws MailException
     */
    public function send()
    {
        $senders = [];
        foreach ($this->getSenders() as $senderEMail => $senderName) {
            $senders[] = $this->addressConverter->convert($senderEMail, $senderName);
        }

        $receivers = [];
        foreach ($this->getReceivers() as $receiverEMail => $receiverName) {
            $receivers[] = $this->addressConverter->convert($receiverEMail, $receiverName);
        }

        $copyReceivers = [];
        foreach ($this->getCopyReceivers() as $receiverEMail => $receiverName) {
            $copyReceivers[] = $this->addressConverter->convert($receiverEMail, $receiverName);
        }

        $blindCopyReceivers = [];
        foreach ($this->getBlindCopyReceivers() as $receiverEMail => $receiverName) {
            $blindCopyReceivers[] = $this->addressConverter->convert($receiverEMail, $receiverName);
        }

        $mimePart =
            $this->mimePartInterfaceFactory->create(['content' => $this->getBody(), 'type' => $this->getType()]);

        $messageData = [
            'from'     => $senders,
            'to'       => $receivers,
            'cc'       => $copyReceivers,
            'bcc'      => $blindCopyReceivers,
            'encoding' => $mimePart->getCharset(),
            'subject'  => html_entity_decode($this->getSubject(), ENT_QUOTES),
            'body'     => $this->mimeMessageInterfaceFactory->create(['parts' => [$mimePart]])
        ];

        $message = $this->emailMessageFactory->create($messageData);

        foreach ($this->getAdditionalHeaders() as $headerName => $headerValue) {
            $message->addHeaderLine($headerName, $headerValue);
        }

        $mailTransport = $this->mailTransportFactory->create(['message' => clone $message]);

        $mailTransport->sendMessage();
    }
}
