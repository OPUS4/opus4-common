<?php

namespace OpusTest\Common\Util;

use Opus\Common\Config;
use Opus\Common\Util\DocumentHelper;
use OpusTest\Common\TestAsset\TestCase;
use Zend_Config;

class DocumentHelperTest extends TestCase
{
    /** @var DocumentHelper */
    private $documentHelper;

    public function setUp(): void
    {
        parent::setUp();
        $this->documentHelper = new DocumentHelper();
    }

    public function testIndexYearDefaultConfig()
    {
        $helper = $this->documentHelper;

        $this->assertEquals('2010', $helper->getYear(2010, 2011, 2012, 2013));
        $this->assertEquals('2011', $helper->getYear('', 2011, 2012, 2013));
        $this->assertEquals('', $helper->getYear('', '', 2012, 2013));
        $this->assertEquals('', $helper->getYear('', '', '', 2013));
    }

    public function testIndexYearCustomConfig()
    {
        $helper = $this->documentHelper;

        Config::set(new Zend_Config([
            'search' => [
                'index' => [
                    'field' => [
                        'year' => [
                            'order' => 'PublishedDate,PublishedYear,CompletedDate,CompletedYear',
                        ],
                    ],
                ],
            ],
        ]));

        $this->assertEquals('2010', $helper->getYear(2010, 2011, 2012, 2013));
        $this->assertEquals('2011', $helper->getYear('', 2011, 2012, 2013));
        $this->assertEquals('2012', $helper->getYear('', '', 2012, 2013));
        $this->assertEquals('2013', $helper->getYear('', '', '', 2013));
    }
}
