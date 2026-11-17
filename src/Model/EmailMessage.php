<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\SimpleMail\Model;

/**
 * The e-mail message of Magento with additional headers.
 *
 * Magento 2.4.8 and newer build the message with Symfony Mime, older versions with Laminas Mail.
 */
class EmailMessage extends \Magento\Framework\Mail\EmailMessage
{
    /**
     * Adds a header, for example "X-Sender". Without value, the name is the complete header line ("Name: value").
     *
     * @param string $headerFieldNameOrLine
     * @param string|null $fieldValue
     *
     * @return void
     */
    public function addHeaderLine(string $headerFieldNameOrLine, ?string $fieldValue = null): void
    {
        if ($fieldValue === null && str_contains($headerFieldNameOrLine, ':')) {
            [$headerFieldNameOrLine, $fieldValue] = explode(':', $headerFieldNameOrLine, 2);
        }

        $headerName = trim($headerFieldNameOrLine);
        $headerValue = trim((string)$fieldValue);

        if (method_exists($this, 'getSymfonyMessage')) {
            $this->getSymfonyMessage()->getHeaders()->addTextHeader($headerName, $headerValue);
        } elseif (property_exists($this, 'zendMessage')) {
            $this->zendMessage->getHeaders()->addHeaderLine($headerName, $headerValue);
        }
    }
}
