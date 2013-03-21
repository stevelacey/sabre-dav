<?php

namespace Sabre\CalDAV\XML\Property;

use Sabre\CalDAV;
use Sabre\DAV;

class InviteTest extends \PHPUnit_Framework_TestCase {

    function testSimple() {

        $sccs = new Invite(array());

    }

    /**
     * @depends testSimple
     */
    function testSerialize() {

        $property = new Invite(array(
            array(
                'href' => 'mailto:user1@example.org',
                'status' => CalDAV\SharingPlugin::STATUS_ACCEPTED,
                'readOnly' => false,
            ),
            array(
                'href' => 'mailto:user2@example.org',
                'commonName' => 'John Doe',
                'status' => CalDAV\SharingPlugin::STATUS_DECLINED,
                'readOnly' => true,
            ),
            array(
                'href' => 'mailto:user3@example.org',
                'commonName' => 'Joe Shmoe',
                'status' => CalDAV\SharingPlugin::STATUS_NORESPONSE,
                'readOnly' => true,
                'summary' => 'Something, something',
            ),
            array(
                'href' => 'mailto:user4@example.org',
                'commonName' => 'Hoe Boe',
                'status' => CalDAV\SharingPlugin::STATUS_INVALID,
                'readOnly' => true,
            ),
        ), array(
            'href' => 'mailto:thedoctor@example.org',
            'commonName' => 'The Doctor',
            'firstName' => 'The',
            'lastName' => 'Doctor',
        ));

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
        $xml = $xmlUtil->write(['{DAV:}root' => $property]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns" xmlns:cal="' . CalDAV\Plugin::NS_CALDAV . '" xmlns:cs="' . CalDAV\Plugin::NS_CALENDARSERVER . '">
  <cs:organizer>
    <d:href>mailto:thedoctor@example.org</d:href>
    <cs:common-name>The Doctor</cs:common-name>
    <cs:first-name>The</cs:first-name>
    <cs:last-name>Doctor</cs:last-name>
  </cs:organizer>
  <cs:user>
    <d:href>mailto:user1@example.org</d:href>
    <cs:invite-accepted/>
    <cs:access>
      <cs:read-write/>
    </cs:access>
  </cs:user>
  <cs:user>
    <d:href>mailto:user2@example.org</d:href>
    <cs:common-name>John Doe</cs:common-name>
    <cs:invite-declined/>
    <cs:access>
      <cs:read/>
    </cs:access>
  </cs:user>
  <cs:user>
    <d:href>mailto:user3@example.org</d:href>
    <cs:common-name>Joe Shmoe</cs:common-name>
    <cs:invite-noresponse/>
    <cs:access>
      <cs:read/>
    </cs:access>
    <cs:summary>Something, something</cs:summary>
  </cs:user>
  <cs:user>
    <d:href>mailto:user4@example.org</d:href>
    <cs:common-name>Hoe Boe</cs:common-name>
    <cs:invite-invalid/>
    <cs:access>
      <cs:read/>
    </cs:access>
  </cs:user>
</d:root>
', $xml);

    }

    /**
     * @depends testSerialize
     */
    public function testUnserialize() {

        $input = array(
            array(
                'href' => 'mailto:user1@example.org',
                'status' => CalDAV\SharingPlugin::STATUS_ACCEPTED,
                'readOnly' => false,
                'commonName' => '',
                'summary' => '',
            ),
            array(
                'href' => 'mailto:user2@example.org',
                'commonName' => 'John Doe',
                'status' => CalDAV\SharingPlugin::STATUS_DECLINED,
                'readOnly' => true,
                'summary' => '',
            ),
            array(
                'href' => 'mailto:user3@example.org',
                'commonName' => 'Joe Shmoe',
                'status' => CalDAV\SharingPlugin::STATUS_NORESPONSE,
                'readOnly' => true,
                'summary' => 'Something, something',
            ),
            array(
                'href' => 'mailto:user4@example.org',
                'commonName' => 'Hoe Boe',
                'status' => CalDAV\SharingPlugin::STATUS_INVALID,
                'readOnly' => true,
                'summary' => '',
            ),
        );

        // Creating the xml
        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALDAV] = 'cal';
        $xmlUtil->namespaceMap[CalDAV\Plugin::NS_CALENDARSERVER] = 'cs';
        $xml = $xmlUtil->write(['{DAV:}root' => new Invite($input)]);

        // Parsing it again
        $xmlUtil->elementMap['{DAV:}root'] = 'Sabre\\CalDAV\\XML\\Property\\Invite';
        $outputProperty = $xmlUtil->parse($xml);
        $this->assertEquals($input, $outputProperty->getValue());

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testUnserializeNoStatus() {

$xml = '<?xml version="1.0"?>
<d:root xmlns:d="DAV:" xmlns:cal="' . CalDAV\Plugin::NS_CALDAV . '" xmlns:cs="' . CalDAV\Plugin::NS_CALENDARSERVER . '">
  <cs:user>
    <d:href>mailto:user1@example.org</d:href>
    <!-- <cs:invite-accepted/> -->
    <cs:access>
      <cs:read-write/>
    </cs:access>
  </cs:user>
</d:root>';

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->elementMap['{DAV:}root'] = 'Sabre\\CalDAV\\XML\\Property\\Invite';
        $xmlUtil->parse($xml);

    }

}
