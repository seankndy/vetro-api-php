<?php
namespace SeanKndy\VetroApi;

use GuzzleHttp\Client as HttpClient;

class Client
{
    /**
     * @var HttpClient
     */
    private $http;


    /**
     * @param string $token API token from VETRO
     * @param string $baseUrl Base URL of VETRO API
     */
    public function __construct(string $token, string $baseUrl = 'https://vetro.io/api/')
    {
        $this->http = new HttpClient([
            'base_uri' => $baseUrl,
            'headers' => ['Token' => $token]
        ]);
    }

    /**
     * Make request to VETRO
     *
     * @var string $token
     * @var string $method GET, POST, etc
     * @var string $endpoint
     * @var array $body Body (will be json-encoded)
     * @var array $parameters Required parameters
     * @var array $optionalParameters Optional parameters
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function sendRequest(string $method, string $endpoint, array $body = [],
        array $parameters = [], array $optionalParameters = [])
    {
        if ($parameters) {
            \array_walk($parameters, function (&$item) {
                \urlencode($item);
            });
            $endpoint .= '/'.\implode('/', $parameters);
        }
        if ($optionalParameters) {
            $endpoint .= '?'.\http_build_query($optionalParameters);
        }

        $resp = $this->http->request($method, $endpoint, ['json' => $body]);
        if ($resp->getStatusCode() < 200 || $resp->getStatusCode() > 299) {
            throw new \Exception("Received non-OK HTTP response: " . $resp->getStatusCode());
        }
        return $resp;
    }

    /**
     * Get network overview
     *
     * @return object
     */
    public function networkOverview()
    {
        $resp = $this->sendRequest('GET', 'network');
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Get network points
     *
     * @param int $planId ID of Plan
     * @param string $format  Format, geojson only one supported currently
     * @return object
     */
    public function networkPoints(int $planId, $format = 'geojson')
    {
        $resp = $this->sendRequest('GET', 'network', [], [$planId, 'points.'.$format]);
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Get network lines
     *
     * @param int $planId ID of Plan
     * @param string $format  Format, geojson only one supported currently
     * @return object
     */
    public function networkLines(int $planId, $format = 'geojson')
    {
        $resp = $this->sendRequest('GET', 'network', [], [$planId, 'lines.'.$format]);
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Get circuits
     *
     * @param int $planIdFilter Filter to a certain Plan
     * @return object
     */
    public function circuits(int $planIdFilter = 0)
    {
        $params = ['list'];
        if ($planIdFilter) {
            $params[] = $planIdFilter;
        }
        $resp = $this->sendRequest('GET', 'circuits', [], $params);
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Get circuit detail
     *
     * @param mixed $circuitId
     * @return object
     */
    public function circuitDetail($circuitId)
    {
        $resp = $this->sendRequest('GET', 'circuit', [], ['info', $circuitId]);
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Get layers
     *
     * @return object
     */
    public function layers()
    {
        $resp = $this->sendRequest('GET', 'layers');
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Find features near a latitude/longitude
     *
     * @param int[] $layerIds List of layer IDs
     * @param float $lat Latitude
     * @param float $lon Longitude
     * @return object
     */
    public function findFeaturesNearLatLon(array $layerIds, float $lat, float $lon)
    {
        $resp = $this->sendRequest('GET', 'layer/geometriesNear', [], [\implode(',', $layerIds), $lat, $lon]);
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Get all zones
     *
     * @return object
     */
    public function zones()
    {
        $resp = $this->sendRequest('GET', 'demand/zones');
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Get zone detail
     *
     * @param int[] $zoneIds
     * @return object
     */
    public function zoneDetail(array $zoneIds)
    {
        $resp = $this->sendRequest('GET', 'demand/zone/details', [\implode(',', $zoneIds)]);
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }

    /**
     * Get address status in the context of zones
     *
     * @param string $address Full address
     * @return object
     */
    public function zoneAddressStatus(string $address)
    {
        $resp = $this->sendRequest('GET', 'demand/addressstatus', [], [$address]);
        $result = \json_decode((string)$resp->getBody());
        return $result;
    }
}
