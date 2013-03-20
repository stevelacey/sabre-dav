<?php

namespace Sabre\DAV\XML\Property;

use Sabre\DAV;

class ResourceTypeTest extends \PHPUnit_Framework_TestCase {

    function testConstruct() {

        $resourceType = new ResourceType(array('{DAV:}collection'));
        $this->assertEquals(array('{DAV:}collection'),$resourceType->getValue());

        $resourceType = new ResourceType(null);
        $this->assertEquals(array(),$resourceType->getValue());

        $resourceType = new ResourceType('{DAV:}collection');
        $this->assertEquals(array('{DAV:}collection'),$resourceType->getValue());

        $resourceType = new ResourceType('{DAV:}principal');
        $this->assertEquals(array('{DAV:}principal'),$resourceType->getValue());

    }

    /**
     * @depends testConstruct
     */
    function testSerialize() {

        $resourceType = new ResourceType(array('{DAV:}collection','{DAV:}principal'));

        $xmlUtil = new DAV\XMLUtil();
        $xml = $xmlUtil->write([
            '{DAV:}anything' => $resourceType
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:anything xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <d:collection/>
  <d:principal/>
</d:anything>
', $xml);

    }

    /**
     * @depends testSerialize
     */
    function testSerializeCustomNS() {

        $resourceType = new ResourceType(array('{http://example.org/NS}article'));

        $xmlUtil = new DAV\XMLUtil();
        $xml = $xmlUtil->write([
            '{DAV:}anything' => $resourceType
        ]);

        $this->assertEquals(
'<?xml version="1.0"?>
<d:anything xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
  <x1:article xmlns:x1="http://example.org/NS"/>
</d:anything>
', $xml);

    }

    /**
     * @depends testConstruct
     */
    function testIs() {

        $resourceType = new ResourceType(array('{DAV:}collection','{DAV:}principal'));
        $this->assertTrue($resourceType->is('{DAV:}collection'));
        $this->assertFalse($resourceType->is('{DAV:}blabla'));

    }

    /**
     * @depends testConstruct
     */
    function testAdd() {

        $resourceType = new ResourceType(array('{DAV:}collection','{DAV:}principal'));
        $resourceType->add('{DAV:}foo');
        $this->assertEquals(array('{DAV:}collection','{DAV:}principal','{DAV:}foo'), $resourceType->getValue());

    }

    /**
     * @depends testConstruct
     */
    function testUnserialize() {

        $xml ='<?xml version="1.0"?>
<d:anything xmlns:d="DAV:"><d:collection/><d:principal/></d:anything>
';

        $xmlUtil = new DAV\XMLUtil();
        $xmlUtil->elementMap['{DAV:}anything'] = 'Sabre\\DAV\\XML\\Property\\ResourceType';

        $resourceType = $xmlUtil->parse($xml);
        $this->assertEquals(array('{DAV:}collection','{DAV:}principal'),$resourceType->getValue());

    }

}
