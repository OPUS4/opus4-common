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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Common\Mail;

use Opus\Common\Config;
use Opus\Common\Mail\MailException;
use Opus\Common\Mail\SendMail;
use OpusTest\Common\TestAsset\TestCase;
use Zend_Config;

/**
 * Test cases for class Opus\Mail.
 *
 * @group    MailSendMailTest
 */
class SendMailTest extends TestCase
{
    /** @var Zend_Config */
    protected $configDummy;

    /**
     * Set up test fixtures.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->configDummy = new Zend_Config([
            'mail' => [
                'opus' => [
                    'smtp' => 'host.does.not.exists.hopefully',
                    'port' => 22,
                ],
            ],
        ]);
    }

    /**
     * Test construtor.
     */
    public function testConstructor()
    {
        Config::set($this->configDummy);
        $mail = new SendMail();

        $this->assertTrue($mail instanceof SendMail);

        // TODO: What else do we espect here?
    }

    /**
     * Test construtor without config.
     */
    public function testConstructorWoConfig()
    {
        Config::set(new Zend_Config([]));
        $mail = new SendMail();

        $this->assertTrue($mail instanceof SendMail);

        // TODO: What else do we espect here?
    }

    /**
     * Test sending mail.
     */
    public function testSendmailWoParameters()
    {
        Config::set(new Zend_Config([]));
        $mail = new SendMail();
        $this->expectException(MailException::class);
        $mail->sendMail(null, null, null, null, null);
    }

    /**
     * Test sending mail.
     */
    public function testSendmailRemoteHostDoesNotExist()
    {
        $mail = new SendMail();
        $this->expectException(MailException::class);
        $mail->sendMail(
            'Sender',
            'sender@does.not.exists.hopefully.mil',
            'no subject',
            'no body',
            [
                [
                    'name'    => 'Recipient',
                    'address' => 'sender@does.not.exists.hopefully.mil',
                ],
            ]
        );
    }

    /**
     * Tests the sending of an e-mail, but without mail body.
     */
    public function testSendMailNoMailFrom()
    {
        $mail      = new SendMail();
        $recipient = ['recipients' => ['address' => 'recipient@testmail.de', 'name' => 'John R. Public']];

        $this->expectException(MailException::class);
        $mail->sendMail('', 'John S. Public', 'My subject', 'My Text', $recipient);
    }

    /**
     * Tests the sending of an e-mail, but without mail from.
     */
    public function testSendMailNoMailBody()
    {
        $mail      = new SendMail();
        $recipient = ['recipients' => ['address' => 'recipient@testmail.de', 'name' => 'John R. Public']];

        $this->expectException(MailException::class);
        $mail->sendMail('recipient@testmail.de', 'John S. Public', '', 'My Text', $recipient);
    }

    /**
     * Tests the sending of an e-mail.
     */
    public function testSendMailSuccess()
    {
        $recipient = ['recipients' => ['address' => 'recipient@testmail.de', 'name' => 'John R. Public']];

        $config = Config::get();
        if (! isset($config, $config->mail->opus)) {
            $this->markTestSkipped('Test mail server is not configured yet.');
        }

        $mail = new SendMail();
        $mail->sendMail('recipient@testmail.de', 'John S. Public', 'Mail Body', 'My Text', $recipient);
    }
}
