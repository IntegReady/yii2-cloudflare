<?php
/**
 * @version 0.0.2
 * @author IntegReady
 * @link https://github.com/nikitich/yii2-cloudflare
 * @license http://www.gnu.org/licenses/lgpl.html LGPL v3 or later
 */

namespace integready\cloudflare;

use yii\base\Component;

/**
 * Class CloudflareActions
 * @package integready\cloudflare
 *
 * @property mixed $listZones
 * @property array $activeZones
 */
class CloudflareActions extends Component
{
    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_PATCH  = 'PATCH';
    const HTTP_METHOD_DELETE = 'DELETE';

    public $apiendpoint;
    public $bearer;
    public $sites;

    /**
     * @return mixed
     */
    public function getListZones()
    {
        return $this->makeRequest('zones');
    }

    /**
     * @param string $zoneIdentifier
     * @param array $params
     *
     * @return mixed
     */
    public function listDnsRecords($zoneIdentifier, $params = [])
    {
        return $this->makeRequest("zones/{$zoneIdentifier}/dns_records", $params);
    }

    /**
     * @param string $zoneIdentifier
     * @param string $identifier
     *
     * @return mixed
     */
    public function detailsDnsRecords($zoneIdentifier, $identifier)
    {
        return $this->makeRequest("zones/{$zoneIdentifier}/dns_records/{$identifier}");
    }

    /**
     * @param string $zoneIdentifier
     * @param string $type
     * @param string $name
     * @param string $content
     * @param array $params
     *
     * @return mixed
     */
    public function createDnsRecord($zoneIdentifier, $type, $name, $content, $params = [])
    {
        return $this->makeRequest("zones/{$zoneIdentifier}/dns_records", array_merge($params, [
            'name'    => $name,
            'type'    => $type,
            'content' => $content,
        ]), self::HTTP_METHOD_POST);
    }

    /**
     * @param string $zoneIdentifier
     * @param string $identifier
     * @param string $type
     * @param string $name
     * @param string $content
     * @param array $params
     *
     * @return mixed
     */
    public function updateDnsRecord($zoneIdentifier, $identifier, $type, $name, $content, $params = [])
    {
        return $this->makeRequest("zones/{$zoneIdentifier}/dns_records/{$identifier}", array_merge($params, [
            'name'    => $name,
            'type'    => $type,
            'content' => $content,
        ]), self::HTTP_METHOD_PUT);
    }

    /**
     * @param string $zoneIdentifier
     * @param string $identifier
     *
     * @return mixed
     */
    public function deleteDnsRecord($zoneIdentifier, $identifier)
    {
        return $this->makeRequest("zones/{$zoneIdentifier}/dns_records/{$identifier}", [], self::HTTP_METHOD_DELETE);
    }

    /**
     * Performs request to the server and gets the answer
     *
     * @param string $sURL
     * @param array $aData
     * @param string $httpMethod
     *
     * @return  mixed
     */
    private function makeRequest($sURL, $aData = [], $httpMethod = self::HTTP_METHOD_GET)
    {
        $aFields = [
            'query'    => json_encode($aData),
            'status'   => 0,
            'response' => '',
        ];

        $rCURL = curl_init();
        curl_setopt($rCURL, CURLOPT_URL, "{$this->apiendpoint}{$sURL}");
        curl_setopt($rCURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rCURL, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($rCURL, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($rCURL, CURLOPT_CUSTOMREQUEST, $httpMethod);

        if (!empty($aData)) {
            if ($httpMethod == self::HTTP_METHOD_GET) {
                $sQueryParams = http_build_query($aData);
                curl_setopt($rCURL, CURLOPT_URL, "{$this->apiendpoint}{$sURL}?$sQueryParams");
            } else {
                curl_setopt($rCURL, CURLOPT_POSTFIELDS, json_encode($aData));
            }
        }

        curl_setopt($rCURL, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->bearer,
        ]);

        $sResponse = $aFields['response'] = curl_exec($rCURL);
        curl_close($rCURL);
        $aResponse = json_decode($sResponse, true);

        return $aResponse;
    }

    /**
     * Clear the cache for the specified zone
     *
     * @param string $site domain name of zone, without 'http://' and 'www.'
     *
     * @return mixed
     */
    public function purgeCache($site = '')
    {
        $url       = 'purge_cache';
        $zonesList = $this->getActiveZones();
        if ($site === '') {
            $site = $this->sites[0];
        }
        $url = isset($zonesList[$site]) ? $zonesList[$site] . '/' . $url : $url;

        return $this->makeRequest('zones/' . $url, [
            'purge_everything' => true,
        ], self::HTTP_METHOD_DELETE);
    }

    /**
     * @return array
     */
    private function getActiveZones()
    {
        $result = [];
        $list   = $this->makeRequest('zones');
        foreach ($list['result'] as $item) {
            if ($item['status'] == 'active') {
                $result[$item['name']] = $item['id'];
            }
        }

        return $result;
    }
}
