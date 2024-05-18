<?php

declare(strict_types=1);

namespace Infrangible\SimpleMail\Model;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class EmailMessage
    extends \Magento\Framework\Mail\EmailMessage
{
    public function addHeaderLine(string $headerFieldNameOrLine, ?string $fieldValue = null): void
    {
        $this->zendMessage->getHeaders()->addHeaderLine($headerFieldNameOrLine, $fieldValue);
    }
}
