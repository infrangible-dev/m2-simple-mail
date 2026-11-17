<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\SimpleMail\Test\Unit\Controller\Adminhtml\Mail;

use Infrangible\SimpleMail\Model\Mail;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Phrase;

/**
 * A mail for the tests that is not sent, but fails with an error if one is set.
 */
class SentMail extends Mail
{
    /** @var string|null */
    private ?string $error = null;

    /**
     * Sets the error that sending the mail fails with.
     *
     * @param string|null $error
     *
     * @return void
     */
    public function setError(?string $error): void
    {
        $this->error = $error;
    }

    /**
     * Fails with the error, if there is one.
     *
     * @return void
     * @throws MailException
     */
    public function send(): void
    {
        if ($this->error !== null) {
            throw new MailException(new Phrase($this->error));
        }
    }
}
