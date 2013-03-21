<?php

namespace Sabre\CalDAV\XML\Notification;

use
    Sabre\XML\Element,
    Sabre\XML\Writer;

/**
 * This interface reflects a single notification type.
 *
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
interface NotificationInterface extends Element {

    /**
     * This method serializes the entire notification, as it is used in the
     * response body.
     *
     * @param Writer $writer
     * @return void
     */
    function serializeFullXml(Writer $writer);

    /**
     * Returns a unique id for this notification
     *
     * This is just the base url. This should generally be some kind of unique
     * id.
     *
     * @return string
     */
    function getId();

    /**
     * Returns the ETag for this notification.
     *
     * The ETag must be surrounded by literal double-quotes.
     *
     * @return string
     */
    function getETag();

}
