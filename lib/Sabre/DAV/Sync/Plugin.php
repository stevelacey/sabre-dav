<?php

namespace Sabre\DAV\Sync;

use
    Sabre\DAV,
    Sabre\DAV\XML\Request\SyncCollectionReport;

/**
 * This plugin all WebDAV-sync capabilities to the Server.
 *
 * WebDAV-sync is defined by rfc6578
 *
 * The sync capabilities only work with collections that implement
 * Sabre\DAV\Sync\ISyncCollection.
 *
 * @copyright Copyright (C) 2007-2013 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class Plugin extends DAV\ServerPlugin {

    /**
     * Reference to server object
     *
     * @var DAV\Server
     */
    protected $server;

    const SYNCTOKEN_PREFIX = 'http://sabredav.org/ns/sync/';

    /**
     * Returns a plugin name.
     *
     * Using this name other plugins will be able to access other plugins
     * using \Sabre\DAV\Server::getPlugin
     *
     * @return string
     */
    public function getPluginName() {

        return 'sync';

    }

    /**
     * Initializes the plugin.
     *
     * This is when the plugin registers it's hooks.
     *
     * @param DAV\Server $server
     * @return void
     */
    public function initialize(DAV\Server $server) {

        $this->server = $server;
        $this->server->xml->elementMap['{DAV:}sync-collection'] = 'Sabre\\DAV\\XML\\Request\\SyncCollectionReport';

        $self = $this;

        $server->subscribeEvent('report', function($reportName, $request, $uri) use ($self) {

            if ($reportName === '{DAV:}sync-collection') {
                $this->server->transactionType = 'report-sync-collection';
                $self->syncCollection($uri, $request);
                return false;
            }

        });

        $server->subscribeEvent('beforeGetProperties', array($this, 'beforeGetProperties'));
        $server->subscribeEvent('validateTokens',      array($this, 'validateTokens'));

    }

    /**
     * Returns a list of reports this plugin supports.
     *
     * This will be used in the {DAV:}supported-report-set property.
     * Note that you still need to subscribe to the 'report' event to actually
     * implement them
     *
     * @param string $uri
     * @return array
     */
    public function getSupportedReportSet($uri) {

        $node = $this->server->tree->getNodeForPath($uri);
        if ($node instanceof ISyncCollection && $node->getSyncToken()) {
            return array(
                '{DAV:}sync-collection',
            );
        }

        return array();

    }


    /**
     * This method handles the {DAV:}sync-collection HTTP REPORT.
     *
     * @param string $uri
     * @param SyncCollectionReport $dom
     * @return void
     */
    public function syncCollection($uri, SyncCollectionReport $request) {

        // rfc3253 specifies 0 is the default value for Depth:
        $depth = $this->server->getHTTPDepth(0);

        $syncLevel = $request->syncLevel;
        if (is_null($syncLevel)) {
            // In case there was no sync-level, it could mean that we're dealing
            // with an old client. For these we must use the depth header
            // instead.
            $syncLevel = $depth;
        }

        // Getting the data
        $node = $this->server->tree->getNodeForPath($uri);
        if (!$node instanceof ISyncCollection) {
            throw new DAV\Exception\ReportNotSupported('The {DAV:}sync-collection REPORT is not supported on this url.');
        }
        $token = $node->getSyncToken();
        if (!$token) {
            throw new DAV\Exception\ReportNotSupported('No sync information is available at this node');
        }

        if (!is_null($request->syncToken)) {
            // Sync-token must start with our prefix
            if (substr($request->syncToken, 0, strlen(self::SYNCTOKEN_PREFIX)) !== self::SYNCTOKEN_PREFIX) {
                throw new DAV\Exception\InvalidSyncToken('Invalid or unknown sync token');
            }

            $syncToken = substr($request->syncToken, strlen(self::SYNCTOKEN_PREFIX));

        } else {
            $syncToken = null;
        }
        $changeInfo = $node->getChanges($syncToken, $syncLevel, $request->limit);

        if (is_null($changeInfo)) {

            throw new DAV\Exception\InvalidSyncToken('Invalid or unknown sync token');

        }

        // Encoding the response
        $this->sendSyncCollectionResponse(
            $changeInfo['syncToken'],
            $uri,
            $changeInfo['added'],
            $changeInfo['modified'],
            $changeInfo['deleted'],
            $request->properties
        );

    }

    /**
     * Sends the response to a sync-collection request.
     *
     * @param string $syncToken
     * @param string $collectionUrl
     * @param array $added
     * @param array $modified
     * @param array $deleted
     * @param array $properties
     * @return void
     */
    protected function sendSyncCollectionResponse($syncToken, $collectionUrl, array $added, array $modified, array $deleted, array $properties) {

        $fullPaths = [];

        // Pre-fetching children, if this is possible.
        foreach(array_merge($added, $modified) as $item) {
            $fullPath = $collectionUrl . '/' . $item;
            $fullPaths[] = $fullPath;
        }

        $responses = [];
        foreach($this->server->getPropertiesForMultiplePaths($fullPaths, $properties) as $fullPath => $props) {

            // The 'Property_Response' class is responsible for generating a
            // single {DAV:}response xml element.
            $responses[] = new DAV\XML\Element\Response($fullPath, $props);

        }

        // Deleted items also show up as 'responses'. They have no properties,
        // and a single {DAV:}status element set as 'HTTP/1.1 404 Not Found'.
        foreach($deleted as $item) {

            $fullPath = $collectionUrl . '/' . $item;
            $responses[] = new DAV\XML\Element\Response($fullPath, array(), 404);

        }


        $writer = $this->server->xml->getWriter();
        $multiStatus = new DAV\XML\Response\MultiStatus($responses, self::SYNCTOKEN_PREFIX . $syncToken);
        $writer->write(['{DAV:}multistatus' => $multiStatus]);

        $this->server->httpResponse->sendStatus(207);
        $this->server->httpResponse->setHeader('Content-Type','application/xml; charset=utf-8');
        $this->server->httpResponse->sendBody($writer->outputMemory());

    }

    /**
     * This method is triggered whenever properties are requested for a node.
     * We intercept this to see if we can must return a {DAV:}sync-token.
     *
     * @param string $path
     * @param DAV\INode $node
     * @param array $requestedProperties
     * @param array $returnedProperties
     * @return void
     */
    public function beforeGetProperties($path, DAV\INode $node, array &$requestedProperties, array &$returnedProperties) {

        if (!in_array('{DAV:}sync-token', $requestedProperties)) {
            return;
        }

        if ($node instanceof ISyncCollection && $token = $node->getSyncToken()) {
            // Unsetting the property from requested properties.
            $index = array_search('{DAV:}sync-token', $requestedProperties);
            unset($requestedProperties[$index]);
            $returnedProperties[200]['{DAV:}sync-token'] = self::SYNCTOKEN_PREFIX . $token;
        }

    }

    /**
     * The validateTokens event is triggered before every request.
     *
     * It's a moment where this plugin can check all the supplied lock tokens
     * in the If: header, and check if they are valid.
     *
     * @param mixed $conditions
     * @return void
     */
    public function validateTokens( &$conditions ) {

        foreach($conditions as $kk=>$condition) {

            foreach($condition['tokens'] as $ii=>$token) {

                // Sync-tokens must always start with our designated prefix.
                if (substr($token['token'], 0, strlen(self::SYNCTOKEN_PREFIX)) !== self::SYNCTOKEN_PREFIX) {
                    continue;
                }

                // Checking if the token is a match.
                $node = $this->server->tree->getNodeForPath($condition['uri']);

                if (
                    $node instanceof ISyncCollection &&
                    $node->getSyncToken() == substr($token['token'], strlen(self::SYNCTOKEN_PREFIX))
                ) {
                    $conditions[$kk]['tokens'][$ii]['validToken'] = true;
                }

            }

        }

    }

}

