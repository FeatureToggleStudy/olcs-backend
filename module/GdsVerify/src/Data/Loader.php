<?php

namespace Dvsa\Olcs\GdsVerify\Data;

/**
 * Class Loader
 * @package Dvsa\Olcs\GdsVerify\Data
 */
class Loader
{
    /**
     * @var \Zend\Http\Client
     */
    private $httpClient;

    /**
     * @var \Zend\Cache\Storage\StorageInterface
     */
    private $cacheAdapter;

    /**
     * Loader constructor.
     *
     * @param \Zend\Cache\Storage\StorageInterface|null $cacheAdapter Cache adapter
     */
    public function __construct(\Zend\Cache\Storage\StorageInterface $cacheAdapter = null)
    {
        if ($cacheAdapter !== null) {
            $this->setCacheAdapter($cacheAdapter);
        }
    }

    /**
     * Load metadata document
     *
     * @param string $pathOrUrl URL or local file path of document
     *
     * @return string
     * @throws \Dvsa\Olcs\GdsVerify\Exception
     */
    private function load($pathOrUrl)
    {
        $cacheKey = md5($pathOrUrl);
        if ($this->isCacheEnable() && $this->getCacheAdapter()->hasItem($cacheKey)) {
            return $this->getCacheAdapter()->getItem($cacheKey);
        }

        if (file_exists($pathOrUrl)) {
            $xml = file_get_contents($pathOrUrl);
        } else {
            $client = $this->getHttpClient();
            $client->setUri($pathOrUrl);
            $response = $this->getHttpClient()->send();
            if (!$response->isOk()) {
                throw new \Dvsa\Olcs\GdsVerify\Exception('Error getting metadata document '. $pathOrUrl);
            }
            $xml = $response->getBody();
        }

        if ($this->isCacheEnable()) {
            $this->getCacheAdapter()->setItem($cacheKey, $xml);
        }

        return $xml;
    }

    /**
     * Load federation metadata document
     *
     * @param string $pathOrUrl URL or local file path of document
     *
     * @return Metadata\Federation
     */
    public function loadFederationMetadata($pathOrUrl)
    {
        return new Metadata\Federation($this->load($pathOrUrl));
    }

    /**
     * Load matching service adapter metadata document
     *
     * @param string $pathOrUrl URL or local file path of document
     *
     * @return Metadata\MatchingServiceAdapter
     */
    public function loadMatchingServiceAdapterMetadata($pathOrUrl)
    {
        return new Metadata\MatchingServiceAdapter($this->load($pathOrUrl));
    }

    /**
     * Is caching enabled
     *
     * @return bool
     */
    private function isCacheEnable()
    {
        return $this->getCacheAdapter() !== null;
    }

    /**
     * Get the cache adapter
     *
     * @return \Zend\Cache\Storage\StorageInterface|null
     */
    public function getCacheAdapter()
    {
        return $this->cacheAdapter;
    }

    /**
     * Set the cache adapter
     *
     * @param \Zend\Cache\Storage\StorageInterface $cacheAdapter Cache adapter
     *
     * @return void
     */
    public function setCacheAdapter(\Zend\Cache\Storage\StorageInterface $cacheAdapter)
    {
        $this->cacheAdapter = $cacheAdapter;
    }

    /**
     * Get the HTTP client
     *
     * @return \Zend\Http\Client
     */
    public function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->setHttpClient(new \Zend\Http\Client());
        }
        $this->httpClient->reset();

        return $this->httpClient;
    }

    /**
     * Set the Http client
     *
     * @param \Zend\Http\Client $client Http client
     *
     * @return void
     */
    public function setHttpClient(\Zend\Http\Client $client)
    {
        $this->httpClient = $client;
    }
}
