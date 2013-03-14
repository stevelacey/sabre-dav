<?php

namespace Sabre\DAVACL\XML\Property;

use
    Sabre\XML\Element,
    Sabre\XML\Reader,
    Sabre\XML\Writer,
    Sabre\DAVACL\XML\Element\Principal,
    Sabre\DAV\Exception\CannotSerialize,
    Sabre\DAV\Exception\BadRequest,
    Sabre\DAV\Exception\NotImplemented;

/**
 * Acl Property
 *
 * This class represents {DAV:}acl property, as defined in:
 *
 * http://tools.ietf.org/html/rfc3744#section-5.5
 *
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Acl implements Element {

    /**
     * __construct
     *
     * You must pass an array with aces. An ace is an 'access control entry'.
     * Every ace has the following properties:
     *
     *   - A privilege, represented as for example {DAV:}read.
     *   - A principal, which must be an instance of
     *     Sabre\DAVACL\XML\Element\Principal.
     *   - protected, either true or false.
     *
     * @param array $acl
     */
    public function __construct(array $acl) {

        $this->acl = $acl;

    }

    /**
     * An array with aces.
     *
     * @var array
     */
    protected $acl;

    /**
     * Returns the current ACL list.
     *
     * @return array
     */
    public function getAcl() {

        return $this->acl;

    }

    /**
     * The serialize method is called during xml writing.
     *
     * It should use the $writer argument to encode this object into XML.
     *
     * Important note: it is not needed to create the parent element. The
     * parent element is already created, and we only have to worry about
     * attributes, child elements and text (if any).
     *
     * Important note 2: If you are writing any new elements, you are also
     * responsible for closing them.
     *
     * @param Writer $writer
     * @return void
     */
    public function serializeXml(Writer $writer) {

        foreach($this->getAcl() as $ace) {

            $writer->startElement('{DAV:}ace');
            $writer->startElement('{DAV:}principal');

            switch($ace['principal']) {
                case '{DAV:}authenticated' :
                case '{DAV:}unauthenticated' :
                case '{DAV:}self' :
                case '{DAV:}all' :
                    $writer->writeElement($ace['principal']);
                    break;
                default :
                    // Assuming it's a href
                    $writer->writeElement('{DAV:}href', $writer->baseUri . trim($ace['principal'],'/') . '/');
            }
            $writer->endElement(); // {DAV:}principal


            $writer->startElement('{DAV:}grant');
            $writer->startElement('{DAV:}privilege');
            $writer->writeElement($ace['privilege']);
            $writer->endElement(); // {DAV:}privilege
            $writer->endElement(); // {DAV:}grant

            if (isset($ace['protected']) && $ace['protected']) {
                $writer->writeElement('{DAV:}protected');
            }

            $writer->endElement();

        }

    }

    /**
     * The deserialize method is called during xml parsing.
     *
     * This method is called statictly, this is because in theory this method
     * may be used as a type of constructor, or factory method.
     *
     * Often you want to return an instance of the current class, but you are
     * free to return other data as well.
     *
     * Important note 2: You are responsible for advancing the reader to the
     * next element. Not doing anything will result in a never-ending loop.
     *
     * If you just want to skip parsing for this element altogether, you can
     * just call $reader->next();
     *
     * $reader->parseInnerTree() will parse the entire sub-tree, and advance to
     * the next element.
     *
     * @param Reader $reader
     * @return mixed
     */
    static public function deserializeXml(Reader $reader) {

        $acl = [];

        $tree = $reader->parseInnerTree();

        if($tree) foreach($tree as $elem) {

            if ($elem['name'] === '{DAV:}ace') {

                $privileges = null;
                $principal = null;
                $protected = false;

                foreach($elem['value'] as $aceElemName=>$value) {

                    switch($aceElemName) {

                        case '{DAV:}grant' :
                            $privileges = $value;
                            break;
                        case '{DAV:}principal' :
                            switch($value->getType()) {
                                case Principal::TYPE_AUTHENTICATED :
                                    $principal = '{DAV:}authenticated';
                                    break;
                                case Principal::TYPE_UNAUTHENTICATED :
                                    $principal = '{DAV:}unauthenticated';
                                    break;
                                case Principal::TYPE_ALL :
                                    $principal = '{DAV:}all';
                                    break;
                                case Principal::TYPE_HREF :
                                    $principal = $value->getHref();
                                    break;
                                case Principal::TYPE_PROPERTY :
                                    throw new NotImplemented('{DAV:}property is not yet implemented for privileges');
                                    break;
                                case Principal::TYPE_SELF :
                                    $principal = '{DAV:}self';
                                    break;
                            }
                            break;

                        $newAcl[$k]['principal'] = $newPrincipal;
                            $principal = $value;
                            break;
                        case '{DAV:}protected' :
                            $protected = true;
                            break;
                        case '{DAV:}deny' :
                            throw new NotImplemented('{DAV:}deny is currently not implemented');

                    }

                }

                if (!$principal) {
                    throw new BadRequest('Each {DAV:}ace must have a {DAV:}principal element');
                }
                if (!$privileges) {
                    throw new BadRequest('Each {DAV:}ace must have a {DAV:}grant element with at least 1 privilege.');
                }

                foreach($privileges as $privilege) {

                    $acl[] = [
                        'principal' => $principal,
                        'privilege' => $privilege,
                        'protected' => $protected,
                    ];

                }

            }

        }
        return new self($acl);

    }

}
