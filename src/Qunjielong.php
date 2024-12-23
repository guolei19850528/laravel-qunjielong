<?php

namespace Guolei19850528\Laravel\Qunjielong;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

/**
 * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/
 */
class Qunjielong
{
    /**
     * @var string
     */
    protected string $baseUrl = 'https://openapi.qunjielong.com';

    /**
     * @var string
     */
    protected string $secret = '';

    /**
     * @var string
     */
    protected string $accessToken = '';

    public function getBaseUrl(): string
    {
        if (\str($this->baseUrl)->endsWith('/')) {
            return \str($this->baseUrl)->substr(0, -1)->toString();
        }
        return $this->baseUrl;
    }

    public function setBaseUrl(string $baseUrl): Qunjielong
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): Qunjielong
    {
        $this->secret = $secret;
        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): Qunjielong
    {
        $this->accessToken = $accessToken;
        return $this;
    }


    public function __construct(string $secret = '', string $baseUrl = 'https://openapi.qunjielong.com')
    {
        $this->setSecret($secret);
        $this->setBaseUrl($baseUrl);
        $this->setAccessToken('');
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=71e7934a-afce-4fd3-a897-e2248502cc94
     * @param array|Collection|null $query
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return Qunjielong|$this
     */
    public function token(
        array|Collection|null $query = [],
        string                $url = '/open/auth/token',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null

    ): Qunjielong
    {
        $query = \collect($query);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($query, 'secret', $this->getSecret());
        $response = Http::baseUrl($this->getBaseUrl())
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->get(
                $url,
                $query->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                $this->setAccessToken(\data_get($json, 'data', ''));
            }
        }
        return $this;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=8ec44327-d795-41dd-9b8e-aab6708e8b9f
     * @param array|Collection|null $query
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function getGhomeInfo(
        array|Collection|null $query = [],
        string                $url = '/open/api/ghome/getGhomeInfo',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $query = \collect($query);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($query, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->get(
                $url,
                $query->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    public function tokenWithCache(
        string                                    $key = '',
        \DateTimeInterface|\DateInterval|int|null $ttl = 7100,
        array|Collection|null                     $tokenFuncArgs = []
    ): Qunjielong
    {
        if (\str($key)->isEmpty()) {
            $key = \str('laravel_qunjielong')->append('_access_token_', $this->getSecret())->toString();
        }
        if (\cache()->has($key)) {
            $this->setAccessToken(\cache()->get($key, ''));
        }
        if (!$this->getGhomeInfo()) {
            $this->token(...\collect($tokenFuncArgs)->toArray());
            \cache()->put($key, $this->getAccessToken(), $ttl);
        }
        return $this;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=55313bca-15ac-4c83-b7be-90e936829fe5
     * @param array|Collection|null $data
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function queryActGoods(
        array|Collection|null $data = [],
        string                $url = '/open/api/act_goods/query_act_goods?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $data = \collect($data);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->post(
                $url,
                $data->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=a43156d1-2fa8-4ea6-9fb3-b550ceb7fe44
     * @param array|Collection|null $data
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function queryOrderList(
        array|Collection|null $data = [],
        string                $url = '/open/api//order/all/query_order_list?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $data = \collect($data);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->post(
                $url,
                $data->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=82385ad9-b3c5-4bcb-9e7a-2fbffd9fa69a
     * @param array|Collection|null $data
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function queryOrderInfo(
        array|Collection|null $data = [],
        string                $url = '/open/api/order/single/query_order_info?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $data = \collect($data);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->post(
                $url,
                $data->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=000011fc-68ac-11eb-a95d-1c34da7b354c
     * @param string|int|null $goodsId
     * @param array|Collection|null $query
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function getGoodsDetail(
        string|int|null       $goodsId = null,
        array|Collection|null $query = [],
        string                $url = '/open/api/goods/get_goods_detail/{goodsId}?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $query = \collect($query);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        \data_fill($urlParameters, 'goodsId', $goodsId);
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->get(
                $url,
                $query->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=ff549f11-68ab-11eb-a95d-1c34da7b354c
     * @param array|Collection|null $data
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function goodsAdd(
        array|Collection|null $data = [],
        string                $url = '/open/api/goods/add?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $data = \collect($data);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->post(
                $url,
                $data->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=ffdd33a0-68ab-11eb-a95d-1c34da7b354c
     * @param array|Collection|null $data
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function goodsUpdate(
        array|Collection|null $data = [],
        string                $url = '/open/api/goods/add?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $data = \collect($data);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->post(
                $url,
                $data->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=8b5e15fb-68ab-11eb-a95d-1c34da7b354c
     * @param array|Collection|null $data
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function goodsDetail(
        array|Collection|null $data = [],
        string                $url = '/open/api/goods/detail?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $data = \collect($data);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->post(
                $url,
                $data->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=8b3b96c0-68ab-11eb-a95d-1c34da7b354c
     * @param array|Collection|null $data
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function goodsStockUpdate(
        array|Collection|null $data = [],
        string                $url = '/open/api/goods/stock/update?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $data = \collect($data);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->post(
                $url,
                $data->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }

    /**
     * @see https://console-docs.apipost.cn/preview/b4e4577f34cac87a/1b45a97352d07e60/?target_id=e1171d6b-49f2-4ff5-8bd6-5b87c8290460
     * @param array|Collection|null $data
     * @param string $url
     * @param array|Collection|null $urlParameters
     * @param array|Collection|null $options
     * @param \Closure|null $responseHandler
     * @return array|null
     */
    public function listActInfo(
        array|Collection|null $data = [],
        string                $url = '/open/api/act/list_act_info?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
        array|Collection|null $options = [],
        \Closure|null         $responseHandler = null
    ): array|null
    {
        $data = \collect($data);
        $options = \collect($options);
        $urlParameters = \collect($urlParameters);
        \data_fill($urlParameters, 'accessToken', $this->getAccessToken());
        $response = Http::baseUrl($this->getBaseUrl())
            ->asJson()
            ->withOptions($options->toArray())
            ->withUrlParameters($urlParameters->toArray())
            ->post(
                $url,
                $data->toArray(),
            );
        if ($responseHandler) {
            return value($responseHandler($response));
        }
        if ($response->ok()) {
            $json = $response->json();
            if (Validator::make($json, ['code' => 'required|integer|size:200'])->messages()->isEmpty()) {
                return \data_get($json, 'data', []) ?? null;
            }
        }
        return null;
    }


}
