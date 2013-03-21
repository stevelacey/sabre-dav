<?php

namespace Sabre\DAVACL\XML\Element;

use
    Sabre\XML\Element,
    Sabre\XML\Reader,
    Sabre\XML\Writer,
    Sabre\DAV\Exception\BadRequest,
    Sabre\DAV\Exception\CannotSerialize;

/**
 * Principal
 *
 * This class represents {DAV:}principal element, as defined in:
 *
 * http://tools.ietf.org/html/rfc3744#section-5.5.1
 *
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Principal implements Element {

    /**
     * To specify a not-logged-in user, use the UNAUTHENTICATED principal
     */
    const TYPE_UNAUTHENTICATED = 1;

    /**
     * To specify any principal that is logged in, use AUTHENTICATED
     */
    const TYPE_AUTHENTICATED = 2;

    /**
     * Specific principals can be specified with the HREF
     */
    const TYPE_HREF = 3;

    /**
     * Everybody, basically
     */
    const TYPE_ALL = 4;

    /**
     * The privilege is tied to a property on the resource.
     */
    const TYPE_PROPERTY = 5;

    /**
     * If the acl resource is a principal, then this can be used to assign
     * privileges to the principal to itself.
     */
    const TYPE_SELF = 6;

    /**
     * The principal type.
     *
     * Must be one of the constants in this class.
     *
     * @var int
     */
    protected $type;

    /**
     * If this is a HREF principal, this must contain the url.
     *
     * @var string
     */
    protected $href;

    /**
     * Which property this is tied to. The contents of this property must
     * contain another href.
     *
     * For instance, the value may be {DAV:}owner. The ACL plugin will then
     * check the value of the owner property, and use that to figure out the
     * real principal.
     *
     * @var string
     */
    protected $property;

    /**
     * Creates the property.
     *
     * The first argument must be one of the constants in this class.
     *
     * If the type is HREF, the second value must be a relative url.
     * If the type is PROPERTY, the second value must be a property name.
     *
     * @param int $type
     * @param string|null $value
     */
    public function __construct($type, $value) {

        $this->type = $type;
        if (( $type === self::TYPE_HREF || $type === self::TYPE_PROPERTY ) && is_null($value)) {
            throw new DAV\Exception('The second argument must be specified when the type is HREF or PROPERTY');
        }
        if ($type === self::TYPE_HREF) {
            $this->href = $value;
        }
        if ($type === self::TYPE_PROPERTY) {
            $this->property = $property;
        }

    }

    /**
     * Returns the current principal type.
     *
     * @return int
     */
    public function getType() {

        return $this->type;

    }

    /**
     * If the current principal is a TYPE_HREF, this will return the url.
     *
     * @return string
     */
    public function getHref() {

        return $this->href;

    }

    /**
     * If the current principal is a TYPE_PROPERTY, this will return the
     * property name.
     *
     * @return string
     */
    public function getProperty() {

        return $this->property;

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

        switch($this->type) {

            case self::TYPE_UNAUTHENTICATED :
                $writer->writeElement('{DAV:}unauthenticated');
                break;
            case self::TYPE_AUTHENTICATED :
                $writer->writeElement('{DAV:}authenticated');
                break;
            case self::TYPE_ALL :
                $writer->writeElement('{DAV:}all');
                break;
            case self::TYPE_HREF :
                $writer->writeElement('{DAV:}href', $writer->baseUri . $this->href);
                break;
            case self::TYPE_SELF :
                $writer->writeElement('{DAV:}self');
                break;
            case self::TYPE_PROPERTY :
                $writer->writeElement('{DAV:}property', [ $this->getProperty() => null ]);
                break;

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

        $type = null;
        $value = null;

        foreach($reader->parseInnerTree() as $elem) {

            switch($elem['name']) {
                case '{DAV:}authenticated' :
                    $type = self::TYPE_AUTHENTICATED;
                    break;
                case '{DAV:}unauthenticated' :
                    $type = self::TYPE_UNAUTHENTICATED;
                    break;
                case '{DAV:}all' :
                    $type = self::TYPE_ALL;
                    break;
                case '{DAV:}property' :
                    $type = self::TYPE_PROPERTY;
                    print_r($elem);
                    die();
                    break;
                case '{DAV:}href' :
                    $type = self::TYPE_HREF;
                    $value = $elem['value'];
                    break;
                case '{DAV:}all' :
                    $type = self::TYPE_SELF;
                    break;
            }

        }
        if (is_null($type)) {
            throw new BadRequest('The principal must contain at least one {DAV:}authenticated, {DAV:}unauthenticated, {DAV:}all, {DAV:}href, {DAV:}property or {DAV:}self element');
        }
        return new self($type, $value);

    }

}
