<?php

namespace Sabre\CalDAV\XML\Notification;

use Sabre\CalDAV;
use Sabre\DAV;

class SystemStatusTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider dataProvider
     */
    function testSerializers($notification, $expected1, $expected2) {

        $this->assertEquals('foo', $notification->getId());
        $this->assertEquals('"1"', $notification->getETag());

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';

        $output = $xmlUtil->write([
            '{' . CalDAV\Plugin::NS_CALENDARSERVER . '}root' => $notification,
        ]);
        $this->assertEquals($expected1, $output);

        $writer = $xmlUtil->getWriter();
        $writer->startElement('{' . CalDAV\Plugin::NS_CALENDARSERVER . '}root');
        $notification->serializeFullXml($writer);
        $writer->endElement();

        $this->assertEquals($expected2, $writer->outputMemory());

    }

    function dataProvider() {

        $prelude = '<?xml version="1.0"?>' . "\n" . '<cs:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cs="http://calendarserver.org/ns/">' . "\n";

        return array(

            array(
                new SystemStatus('foo', '"1"'),
                $prelude . '  <cs:systemstatus type="high"/>'."\n".'</cs:root>' . "\n",
                $prelude . '  <cs:systemstatus type="high"/>'."\n".'</cs:root>' . "\n",
            ),

            array(
                new SystemStatus('foo', '"1"', SystemStatus::TYPE_MEDIUM,'bar'),
                $prelude . '  <cs:systemstatus type="medium"/>'."\n".'</cs:root>' . "\n",
                $prelude . '  <cs:systemstatus type="medium">'."\n".'    <cs:description>bar</cs:description>'."\n".'  </cs:systemstatus>'."\n".'</cs:root>' . "\n",
            ),

            array(
                new SystemStatus('foo', '"1"', SystemStatus::TYPE_LOW,null,'http://example.org/'),
                $prelude . '  <cs:systemstatus type="low"/>'."\n".'</cs:root>' . "\n",
                $prelude . '  <cs:systemstatus type="low">'."\n".'    <d:href>http://example.org/</d:href>'."\n".'  </cs:systemstatus>'."\n".'</cs:root>' . "\n",
            )
        );

    }

}
