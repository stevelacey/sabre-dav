<?php

namespace Sabre\CalDAV\XML\Property;

use
    Sabre\DAV,
    Sabre\CalDAV;

class SupportedCalendarComponentSetTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $sccs = new SupportedCalendarComponentSet(array('VEVENT'));
        $this->assertEquals(array('VEVENT'), $sccs->getValue());

    }

    /**
     * @depends testSimple
     */
    function testSerialize() {

        $property = new SupportedCalendarComponentSet(array('VEVENT','VJOURNAL'));

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
        $xml = $xmlUtil->write(['{DAV:}root' => $property]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cal="urn:ietf:params:xml:ns:caldav" xmlns:cs="http://calendarserver.org/ns/">
  <cal:comp name="VEVENT"/>
  <cal:comp name="VJOURNAL"/>
</d:root>
', $xml);

    }

    /**
     * @depends testSimple
     */
    function testUnserializer() {

        $xml = '<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:cal="' . \Sabre\CalDAV\Plugin::NS_CALDAV . '">' .
'<cal:comp name="VEVENT"/>' .
'<cal:comp name="VJOURNAL"/>' .
'</d:root>';

        $dom = \Sabre\DAV\XMLUtil::loadDOMDocument($xml);

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->elementMap['{DAV:}root'] = 'Sabre\\CalDAV\\XML\\Property\\SupportedCalendarComponentSet';
        $property = $xmlUtil->parse($xml);

        $this->assertTrue($property instanceof SupportedCalendarComponentSet);
        $this->assertEquals(array(
            'VEVENT',
            'VJOURNAL',
           ),
           $property->getValue());

    }

}
