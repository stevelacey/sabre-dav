<?php

namespace Sabre\DAV\XML\Request;

class PropPatchTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Sabre\DAV\Server::parsePropPatchRequest
     */
    public function testParse() {

        $body = '<?xml version="1.0"?>
<d:propertyupdate xmlns:d="DAV:" xmlns:s="http://sabredav.org/NS/test">
  <d:set><d:prop><s:someprop>somevalue</s:someprop></d:prop></d:set>
  <d:remove><d:prop><s:someprop2 /></d:prop></d:remove>
  <d:set><d:prop><s:someprop3>removeme</s:someprop3></d:prop></d:set>
  <d:remove><d:prop><s:someprop3 /></d:prop></d:remove>
</d:propertyupdate>';

        $xmlUtil = new \Sabre\DAV\XMLUtil();
        $result = $xmlUtil->parse($body);
        $this->assertEquals(array(
            '{http://sabredav.org/NS/test}someprop' => 'somevalue',
            '{http://sabredav.org/NS/test}someprop2' => null,
            '{http://sabredav.org/NS/test}someprop3' => null,
        ), $result->properties);

    }

}
