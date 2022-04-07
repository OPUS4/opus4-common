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
 * @copyright   Copyright (c) 2017-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

namespace OpusTest\Config;

use Opus\Config;
use Opus\Config\FileTypes;
use OpusTest\TestAsset\TestCase;
use Zend_Config;

class FileTypesTest extends TestCase
{
    private $helper;

    public function setUp()
    {
        parent::setUp();

        Config::set(new Zend_Config([
            'filetypes' => [
                'default' => [
                    'contentDisposition' => 'attachment',
                ],
                'pdf'     => [
                    'mimeType'           => 'application/pdf',
                    'contentDisposition' => 'inline',
                ],
                'txt'     => [
                    'mimeType' => 'text/plain',
                ],
                'htm'     => [
                    'mimeType' => 'text/html',
                ],
                'html'    => [
                    'mimeType' => 'text/html',
                ],
            ],
        ]));

        $this->helper = new FileTypes();
    }

    public function testGetValidMimeTypes()
    {
        $types = $this->helper->getValidMimeTypes();

        $this->assertNotNull($types);
        $this->assertInternalType('array', $types);

        $this->assertArrayHasKey('pdf', $types);
        $this->assertEquals('application/pdf', $types['pdf']);

        $this->assertArrayHasKey('txt', $types);
        $this->assertEquals('text/plain', $types['txt']);

        $this->assertArrayNotHasKey('default', $types);
    }

    public function testMimeTypeAddedToBaseConfigurationFromApplicationIni()
    {
        Config::set(new Zend_Config([
            'filetypes' => [
                'pdf'  => [
                    'mimeType'           => 'application/pdf',
                    'contentDisposition' => 'inline',
                ],
                'txt'  => [
                    'mimeType' => 'text/plain',
                ],
                'htm'  => [
                    'mimeType' => 'text/html',
                ],
                'html' => [
                    'mimeType' => 'text/html',
                ],
                'xml'  => [
                    'mimeType' => [
                        'text/xml',
                        'application/xml',
                    ],
                ],
            ],
        ]));

        $types = $this->helper->getValidMimeTypes();

        $this->assertNotNull($types);
        $this->assertCount(5, $types);
        $this->assertArrayHasKey('xml', $types);

        $xmlTypes = $types['xml'];

        $this->assertCount(2, $xmlTypes);
        $this->assertContains('application/xml', $xmlTypes);
        $this->assertContains('text/xml', $xmlTypes);
    }

    public function testGetContentDisposition()
    {
        $this->assertEquals('attachment', $this->helper->getContentDisposition('text/plain'));
        $this->assertEquals('inline', $this->helper->getContentDisposition('application/pdf'));
    }

    public function testIsValidMimeType()
    {
        $this->assertTrue($this->helper->isValidMimeType('text/plain'));
        $this->assertTrue($this->helper->isValidMimeType('text/html'));

        $this->assertFalse($this->helper->isValidMimeType('text/xslt'));
        $this->assertFalse($this->helper->isValidMimeType('application/doc'));
    }

    public function testIsValidMimeTypeForExtensionWithMultipleTypes()
    {
        Config::set(new Zend_Config([
            'filetypes' => [
                'xml' => [
                    'mimeType' => [
                        'text/xml',
                        'application/xml',
                    ],
                ],
            ],
        ]));

        $this->assertTrue($this->helper->isValidMimeType('application/xml'));
        $this->assertTrue($this->helper->isValidMimeType('text/xml'));
    }

    public function testIsValidMimeTypeForExtension()
    {
        Config::set(new Zend_Config([
            'filetypes' => [
                'pdf' => [
                    'mimeType'           => 'application/pdf',
                    'contentDisposition' => 'inline',
                ],
                'txt' => [
                    'mimeType' => 'text/plain',
                ],
                'xml' => [
                    'mimeType' => [
                        'text/xml',
                        'application/xml',
                    ],
                ],
            ],
        ]));

        $this->assertTrue($this->helper->isValidMimeType('application/xml', 'xml'));
        $this->assertTrue($this->helper->isValidMimeType('text/xml', 'xml'));
        $this->assertTrue($this->helper->isValidMimeType('text/plain', 'txt'));
        $this->assertTrue($this->helper->isValidMimeType('application/pdf', 'pdf'));

        $this->assertFalse($this->helper->isValidMimeType('text/plain', 'xml'));
        $this->assertFalse($this->helper->isValidMimeType('application/pdf', 'doc'));
        $this->assertFalse($this->helper->isValidMimeType('image/jpeg', 'jpeg'));
        $this->assertFalse($this->helper->isValidMimeType('audio/mpeg', 'txt'));
    }

    public function testIsValidMimeTypeForNull()
    {
        $this->assertFalse($this->helper->isValidMimeType(null));
        $this->assertFalse($this->helper->isValidMimeType(null, 'txt'));
    }

    public function testExtensionCaseInsensitive()
    {
        Config::set(new Zend_Config([
            'filetypes' => ['XML' => ['mimeType' => 'text/xml']],
        ]));

        $this->assertTrue($this->helper->isValidMimeType('text/xml', 'xml'));
        $this->assertTrue($this->helper->isValidMimeType('text/xml', 'XML'));
        $this->assertTrue($this->helper->isValidMimeType('text/xml', 'XmL'));
    }
}
