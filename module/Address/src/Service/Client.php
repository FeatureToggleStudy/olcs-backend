<?php

/**
 * Client
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Address\Service;

use Zend\Http\Client as HttpClient;

/**
 * Client
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Client extends HttpClient
{
    private $baseUri;

    public function __construct($baseUri)
    {
        $this->baseUri = rtrim($baseUri, '/');
        $this->setMethod('GET');
    }

    public function setUri($uri)
    {
        $uri = $this->baseUri . '/' . ltrim($uri, '/');

        return parent::setUri($uri);
    }
}
