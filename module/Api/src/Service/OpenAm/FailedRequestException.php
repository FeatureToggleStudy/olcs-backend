<?php

namespace Dvsa\Olcs\Api\Service\OpenAm;

use Zend\Http\Response;

class FailedRequestException extends \Exception
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @param Response $response
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($response, $code = 0, \Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct('Invalid response from OpenAm service: ' . $response->getStatusCode(), $code, $previous);
    }

    /**
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }
}
