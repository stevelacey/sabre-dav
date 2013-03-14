<?php

namespace Sabre\DAVACL\XML\Property;

use
    Sabre\DAV,
    Sabre\HTTP,
    Sabre\XML;


class ACLTest extends \PHPUnit_Framework_TestCase {

    function testConstruct() {

        $acl = new Acl(array());

    }


    function assertXml($expected, Acl $prop) {

        $writer = new XML\Writer();
        $writer->namespaceMap['DAV:'] = 'd';
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');
        $writer->startDocument('1.0','UTF-8');
        $writer->write([
            '{DAV:}acl' => $prop,
        ]);

        $result = $writer->outputMemory();
        $this->assertEquals($expected, $result);

    }

    function parse($input) {

        $reader = new XML\Reader();
        $reader->elementMap = [
            '{DAV:}acl'       => 'Sabre\\DAVACL\\XML\\Property\\Acl',
            '{DAV:}ace'       => 'Sabre\\XML\\Element\\KeyValue',
            '{DAV:}grant'     => 'Sabre\\DAVACL\\XML\\Element\\Grant',
            '{DAV:}principal' => 'Sabre\\DAVACL\\XML\\Element\\Principal',
        ];
        $reader->xml($input);
        return $reader->parse()['value'];

    }

    function testSerializeEmpty() {

        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<d:acl xmlns:d="DAV:"/>
';
        $this->assertXml($expected, new Acl([]));

    }

    function testSerialize() {

        $privileges = array(
            array(
                'principal' => 'principals/evert',
                'privilege' => '{DAV:}write',
                'uri'       => 'articles',
            ),
            array(
                'principal' => 'principals/foo',
                'privilege' => '{DAV:}read',
                'uri'       => 'articles',
                'protected' => true,
            ),
        );

        $acl = new Acl($privileges);

        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<d:acl xmlns:d="DAV:">
  <d:ace>
    <d:principal>
      <d:href>/principals/evert/</d:href>
    </d:principal>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
  </d:ace>
  <d:ace>
    <d:principal>
      <d:href>/principals/foo/</d:href>
    </d:principal>
    <d:grant>
      <d:privilege>
        <d:read/>
      </d:privilege>
    </d:grant>
    <d:protected/>
  </d:ace>
</d:acl>
';
        $this->assertXml($expected, $acl);

    }

    function testSerializeSpecialPrincipals() {

        $privileges = array(
            array(
                'principal' => '{DAV:}authenticated',
                'privilege' => '{DAV:}write',
                'uri'       => 'articles',
            ),
            array(
                'principal' => '{DAV:}unauthenticated',
                'privilege' => '{DAV:}write',
                'uri'       => 'articles',
            ),
            array(
                'principal' => '{DAV:}all',
                'privilege' => '{DAV:}write',
                'uri'       => 'articles',
            ),

        );

        $acl = new Acl($privileges);

        $expected = '<?xml version="1.0" encoding="UTF-8"?>
<d:acl xmlns:d="DAV:">
  <d:ace>
    <d:principal>
      <d:authenticated/>
    </d:principal>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
  </d:ace>
  <d:ace>
    <d:principal>
      <d:unauthenticated/>
    </d:principal>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
  </d:ace>
  <d:ace>
    <d:principal>
      <d:all/>
    </d:principal>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
  </d:ace>
</d:acl>
';
        $this->assertXml($expected, $acl);

    }

    function testUnserialize() {

        $source = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
  <d:ace>
    <d:principal>
      <d:href>/principals/evert/</d:href>
    </d:principal>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
  </d:ace>
  <d:ace>
    <d:principal>
      <d:href>/principals/foo/</d:href>
    </d:principal>
    <d:grant>
      <d:privilege>
        <d:read/>
      </d:privilege>
    </d:grant>
    <d:protected/>
  </d:ace>
</d:acl>
';

        $result = $this->parse($source);

        $this->assertInstanceOf('Sabre\\DAVACL\\XML\\Property\\Acl', $result);

        $expected = array(
            array(
                'principal' => '/principals/evert/',
                'protected' => false,
                'privilege' => '{DAV:}write',
            ),
            array(
                'principal' => '/principals/foo/',
                'protected' => true,
                'privilege' => '{DAV:}read',
            ),
        );

        $this->assertEquals($expected, $result->getAcl());


    }

    /**
     * @expectedException Sabre\DAV\Exception\BadRequest
     */
    function testUnserializeNoPrincipal() {

        $source = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
  <d:ace>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
  </d:ace>
</d:acl>
';

        $result = $this->parse($source);

    }

    function testUnserializeOtherPrincipal() {

        $source = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
  <d:ace>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
    <d:principal><d:authenticated /></d:principal>
  </d:ace>
  <d:ace>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
    <d:principal><d:unauthenticated /></d:principal>
  </d:ace>
  <d:ace>
    <d:grant>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:grant>
    <d:principal><d:all /></d:principal>
  </d:ace>
</d:acl>
';

        $result = $this->parse($source);
        $this->assertInstanceOf('Sabre\\DAVACL\\XML\\Property\\Acl', $result);

        $expected = array(
            array(
                'principal' => '{DAV:}authenticated',
                'protected' => false,
                'privilege' => '{DAV:}write',
            ),
            array(
                'principal' => '{DAV:}unauthenticated',
                'protected' => false,
                'privilege' => '{DAV:}write',
            ),
            array(
                'principal' => '{DAV:}all',
                'protected' => false,
                'privilege' => '{DAV:}write',
            ),
        );

        $this->assertEquals($expected, $result->getAcl());


    }

    /**
     * @expectedException Sabre\DAV\Exception\NotImplemented
     */
    function testUnserializeDeny() {

        $source = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
  <d:ace>
    <d:deny>
      <d:privilege>
        <d:write/>
      </d:privilege>
    </d:deny>
    <d:principal><d:href>/principals/evert</d:href></d:principal>
  </d:ace>
</d:acl>
';

        $this->parse($source);
    }

    /**
     * @expectedException Sabre\DAV\Exception\BadRequest
     */
    function testUnserializeMissingPriv() {

        $source = '<?xml version="1.0"?>
<d:acl xmlns:d="DAV:">
  <d:ace>
    <d:grant>
      <d:privilege />
    </d:grant>
    <d:principal><d:href>/principals/evert</d:href></d:principal>
  </d:ace>
</d:acl>
';

        $this->parse($source);

    }
}
