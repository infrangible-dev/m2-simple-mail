<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\SimpleMail\Test\Unit\Model;

use Infrangible\SimpleMail\Model\EmailMessage;
use Magento\Framework\Mail\Address;
use Magento\Framework\Mail\AddressFactory;
use Magento\Framework\Mail\MimeMessage;
use Magento\Framework\Mail\MimeMessageInterfaceFactory;
use Magento\Framework\Mail\MimePart;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Tests the additional headers with the mail library of the Magento version: Laminas Mail before 2.4.8, Symfony Mime
 * since then.
 */
class EmailMessageTest extends TestCase
{
    /**
     * Returns a message with a receiver and a plain text body.
     *
     * @return EmailMessage
     */
    private function createMessage(): EmailMessage
    {
        return new EmailMessage(
            body: new MimeMessage([new MimePart('Body text', 'text/plain')]),
            to: [new Address('receiver@example.com', 'Receiver')],
            mimeMessageFactory: $this->createStub(MimeMessageInterfaceFactory::class),
            addressFactory: $this->createStub(AddressFactory::class),
            from: [new Address('sender@example.com', null)],
            subject: 'Subject',
            logger: new NullLogger()
        );
    }

    /**
     * Returns the decoded headers of the message as name => value.
     *
     * Laminas Mail encodes the values as UTF-8 words with quoted-printable encoding ("=?UTF-8?Q?Test=20Mail?="),
     * Symfony Mime not.
     *
     * @param EmailMessage $message
     *
     * @return array
     * @phpstan-return array<string, string>
     */
    private function getHeaders(EmailMessage $message): array
    {
        $headers = [];

        foreach (explode("\r\n", explode("\r\n\r\n", $message->getRawMessage(), 2)[ 0 ]) as $line) {
            if (str_contains($line, ': ')) {
                [$name, $value] = explode(': ', $line, 2);

                $headers[ $name ] = (string)preg_replace_callback(
                    '/=\?UTF-8\?Q\?(.*?)\?=/i',
                    fn(array $match): string => quoted_printable_decode(str_replace('_', ' ', $match[ 1 ])),
                    $value
                );
            }
        }

        return $headers;
    }

    /**
     * Tests a header with name and value.
     *
     * @return void
     */
    public function testHeaderWithValue(): void
    {
        $message = $this->createMessage();

        $message->addHeaderLine('X-Sender', 'Test Mail');

        $this->assertSame('Test Mail', $this->getHeaders($message)[ 'X-Sender' ] ?? null);
    }

    /**
     * Tests a complete header line without value.
     *
     * @return void
     */
    public function testHeaderLine(): void
    {
        $message = $this->createMessage();

        $message->addHeaderLine('X-Mailer: Infrangible');

        $this->assertSame('Infrangible', $this->getHeaders($message)[ 'X-Mailer' ] ?? null);
    }
}
