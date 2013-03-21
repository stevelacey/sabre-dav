<?php

namespace Sabre\CalDAV\XML\Notification;

use Sabre\CalDAV;
use Sabre\DAV;

class InviteTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider dataProvider
     */
    function testSerializers($notification, $expected) {

        $notification = new Invite($notification);

        $this->assertEquals('foo', $notification->getId());
        $this->assertEquals('"1"', $notification->getETag());

        $simpleExpected = <<<XML
<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cs="http://calendarserver.org/ns/" xmlns:cal="urn:ietf:params:xml:ns:caldav">
  <cs:invite-notification/>
</d:root>

XML;

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';

        $output = $xmlUtil->write([
            '{DAV:}root' => $notification,
        ]);
        $this->assertEquals($simpleExpected, $output);

        $writer = $xmlUtil->getWriter();
        $writer->startElement('{DAV:}root');
        $notification->serializeFullXml($writer);
        $writer->endElement();

        $this->assertEquals($expected, $writer->outputMemory());

    }

    function dataProvider() {

        $dtStamp = new \DateTime('2012-01-01 00:00:00', new \DateTimeZone('GMT'));
        return array(
            array(
                array(
                    'id' => 'foo',
                    'dtStamp' => $dtStamp,
                    'etag' => '"1"',
                    'href' => 'mailto:foo@example.org',
                    'type' => CalDAV\SharingPlugin::STATUS_ACCEPTED,
                    'readOnly' => true,
                    'hostUrl' => 'calendar',
                    'organizer' => 'principal/user1',
                    'commonName' => 'John Doe',
                    'summary' => 'Awesome stuff!'
                ),
<<<FOO
<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cs="http://calendarserver.org/ns/" xmlns:cal="urn:ietf:params:xml:ns:caldav">
  <cs:dtstamp>20120101T000000Z</cs:dtstamp>
  <cs:invite-notification>
    <cs:uid>foo</cs:uid>
    <d:href>mailto:foo@example.org</d:href>
    <cs:invite-accepted/>
    <cs:hosturl>
      <d:href>/calendar</d:href>
    </cs:hosturl>
    <cs:summary>Awesome stuff!</cs:summary>
    <cs:access>
      <cs:read/>
    </cs:access>
    <cs:organizer>
      <d:href>/principal/user1</d:href>
      <cs:common-name>John Doe</cs:common-name>
    </cs:organizer>
    <cs:organizer-cn>John Doe</cs:organizer-cn>
  </cs:invite-notification>
</d:root>

FOO
            ),
            array(
                array(
                    'id' => 'foo',
                    'dtStamp' => $dtStamp,
                    'etag' => '"1"',
                    'href' => 'mailto:foo@example.org',
                    'type' => CalDAV\SharingPlugin::STATUS_DECLINED,
                    'readOnly' => true,
                    'hostUrl' => 'calendar',
                    'organizer' => 'principal/user1',
                    'commonName' => 'John Doe',
                ),
<<<FOO
<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cs="http://calendarserver.org/ns/" xmlns:cal="urn:ietf:params:xml:ns:caldav">
  <cs:dtstamp>20120101T000000Z</cs:dtstamp>
  <cs:invite-notification>
    <cs:uid>foo</cs:uid>
    <d:href>mailto:foo@example.org</d:href>
    <cs:invite-declined/>
    <cs:hosturl>
      <d:href>/calendar</d:href>
    </cs:hosturl>
    <cs:access>
      <cs:read/>
    </cs:access>
    <cs:organizer>
      <d:href>/principal/user1</d:href>
      <cs:common-name>John Doe</cs:common-name>
    </cs:organizer>
    <cs:organizer-cn>John Doe</cs:organizer-cn>
  </cs:invite-notification>
</d:root>

FOO
            ),
            array(
                array(
                    'id' => 'foo',
                    'dtStamp' => $dtStamp,
                    'etag' => '"1"',
                    'href' => 'mailto:foo@example.org',
                    'type' => CalDAV\SharingPlugin::STATUS_NORESPONSE,
                    'readOnly' => true,
                    'hostUrl' => 'calendar',
                    'organizer' => 'principal/user1',
                    'firstName' => 'Foo',
                    'lastName'  => 'Bar',
                ),
<<<FOO
<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cs="http://calendarserver.org/ns/" xmlns:cal="urn:ietf:params:xml:ns:caldav">
  <cs:dtstamp>20120101T000000Z</cs:dtstamp>
  <cs:invite-notification>
    <cs:uid>foo</cs:uid>
    <d:href>mailto:foo@example.org</d:href>
    <cs:invite-noresponse/>
    <cs:hosturl>
      <d:href>/calendar</d:href>
    </cs:hosturl>
    <cs:access>
      <cs:read/>
    </cs:access>
    <cs:organizer>
      <d:href>/principal/user1</d:href>
      <cs:first-name>Foo</cs:first-name>
      <cs:last-name>Bar</cs:last-name>
    </cs:organizer>
    <cs:organizer-first>Foo</cs:organizer-first>
    <cs:organizer-last>Bar</cs:organizer-last>
  </cs:invite-notification>
</d:root>

FOO
            ),
            array(
                array(
                    'id' => 'foo',
                    'dtStamp' => $dtStamp,
                    'etag' => '"1"',
                    'href' => 'mailto:foo@example.org',
                    'type' => CalDAV\SharingPlugin::STATUS_DELETED,
                    'readOnly' => false,
                    'hostUrl' => 'calendar',
                    'organizer' => 'mailto:user1@fruux.com',
                    'supportedComponents' => new CalDAV\XML\Property\SupportedCalendarComponentSet(array('VEVENT','VTODO')),
                ),
<<<FOO
<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cs="http://calendarserver.org/ns/" xmlns:cal="urn:ietf:params:xml:ns:caldav">
  <cs:dtstamp>20120101T000000Z</cs:dtstamp>
  <cs:invite-notification>
    <cs:uid>foo</cs:uid>
    <d:href>mailto:foo@example.org</d:href>
    <cs:invite-deleted/>
    <cs:hosturl>
      <d:href>/calendar</d:href>
    </cs:hosturl>
    <cs:access>
      <cs:read-write/>
    </cs:access>
    <cs:organizer>
      <d:href>mailto:user1@fruux.com</d:href>
    </cs:organizer>
    <cal:supported-calendar-component-set>
      <cal:comp name="VEVENT"/>
      <cal:comp name="VTODO"/>
    </cal:supported-calendar-component-set>
  </cs:invite-notification>
</d:root>

FOO
            ),

        );

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testMissingArg() {

        new Invite(array());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testUnknownArg() {

        new Invite(array(
            'foo-i-will-break' => true,

            'id' => 1,
            'etag' => '"bla"',
            'href' => 'abc',
            'dtStamp' => 'def',
            'type' => 'ghi',
            'readOnly' => true,
            'hostUrl' => 'jkl',
            'organizer' => 'mno',
        ));

    }
}
