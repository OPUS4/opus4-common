<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace Opus\Common\Mail;

use Opus\Common\ConfigTrait;
use Opus\Common\Log;
use Zend_Mail;
use Zend_Mail_Transport_File;
use Zend_Validate_EmailAddress;

use function is_array;
use function mt_rand;
use function time;
use function trim;

/**
 * Methods to send e-mails via \Zend_Mail, but with mail server from config.ini.
 */
class SendMail
{
    use ConfigTrait;

    /** @var Transport */
    private $transport;

    /**
     * Create a new SendMail instance
     */
    public function __construct()
    {
        $config = $this->getConfig();

        if (isset($config, $config->mail->opus)) {
            if (isset($config->mail->opus->transport) && $config->mail->opus->transport === 'file') {
                // erlaubt das Speichern von E-Mails in Dateien, die im Verzeichnis mail.opus.file abgelegt werden
                $options = [];
                if (isset($config->mail->opus->file)) {
                    $options['path'] = $config->mail->opus->file;
                }
                $callback            = function () {
                    return 'opus-mail_' . time() . '_' . mt_rand() . '.tmp';
                };
                $options['callback'] = $callback;
                $this->transport     = new Zend_Mail_Transport_File($options);
                return;
            }

            $this->transport = new Transport($config->mail->opus);
            return;
        }
        $this->transport = new Transport();
    }

    /**
     * Validates an e-mail address
     *
     * @param   string $address Address
     * @throws  MailException Thrown if the e-mail address is not valid.
     * @return  string              Address
     */
    public static function validateAddress($address)
    {
        $validator = new Zend_Validate_EmailAddress();
        if ($validator->isValid($address) === false) {
            foreach ($validator->getMessages() as $message) {
                throw new MailException($message);
            }
        }

        return $address;
    }

    /**
     * Creates and sends an e-mail to the specified recipient using the SMTP transport.
     * This method should be used carefully, particularly with regard to the possibility
     * of sending mails anonymously to user-defined recipients.
     *
     * @param   string      $from       Sender address
     * @param   string      $fromName   Sender name
     * @param   string      $subject    Subject
     * @param   string      $bodyText   Text
     * @param   array       $recipients Recipients (array [#] => array ('name' => '...', 'address' => '...'))
     * @param   null|string $replyTo
     * @param   null|string $replyToName
     * @param   null|string $returnPath
     * @return  true True if mail was sent
     * @throws MailException Thrown if the mail could not be sent.
     * @throws MailException Thrown if the from address is invalid.
     */
    public function sendMail(
        $from,
        $fromName,
        $subject,
        $bodyText,
        $recipients,
        $replyTo = null,
        $replyToName = null,
        $returnPath = null
    ) {
        $logger = Log::get();

        if ($from === null || trim($from) === '') {
            throw new MailException('No sender address given.');
        }
        self::validateAddress($from);

        if (trim($subject) === '') {
            throw new MailException('No subject text given.');
        }

        $mail = new Zend_Mail('utf-8');
        $mail->setFrom($from, $fromName);
        $mail->setSubject($subject);
        $mail->setBodyText($bodyText);

        if ($replyTo !== null) {
            $mail->setReplyTo($replyTo, $replyToName);
        }

        if ($returnPath !== null) {
            $mail->setReturnPath($returnPath);
        }

        foreach ($recipients as $recip) {
            // TODO should not happen (except in existing tests) - remove test and code?
            if (! is_array($recip)) {
                continue;
            }
            self::validateAddress($recip['address']);
            $logger->debug('SendMail: adding recipient <' . $recip['address'] . '>');
            $mail->addTo($recip['address'], $recip['name']);
        }

        try {
            $mail->send($this->transport);
            $logger->debug('SendMail: Successfully sent mail to ' . $recip['address']);
        } catch (MailException $e) {
            $logger->err('SendMail: Failed sending mail to ' . $recip['address'] . ', error: ' . $e);
            throw new MailException('SendMail: Mail could not be sent.');
        }

        return true;
    }
}
