<?php

namespace Guolei19850528\Laravel\Qunjielong;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class Qunjielong
{
    protected string $baseUrl = 'https://openapi.qunjielong.com';

    protected string $secret = '';
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

    public function queryActGoods(
        array|Collection|null $data = [],
        array|Collection|null $options = [],
        string                $url = '/open/api/act_goods/query_act_goods?accessToken={accessToken}',
        array|Collection|null $urlParameters = null,
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
