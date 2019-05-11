<?php
namespace overtest\Weather;

use GuzzleHttp\Client;
use overtest\Weather\Exceptions\HttpException;
use overtest\Weather\Exceptions\InvalidArgumentException;

class Weather
{
    protected $key;
    protected $guzzleOptions = [];

    public function __construct(string $key)
    {

        $this->key = $key;
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    public function getWeather($city, string $type = 'live', $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        //就 $type 值，'live=>base' 代表实时，'forecast=>all' 代表天气预报，使参数更加语义化
        $types = [
                'live' => 'base',
                'forecast' => 'all',
        ];

        // 1. 对 '$format' 和 '$type' 参数进行检查，不在范围内的抛出异常。
        if (!\in_array($format, ['xml', 'json'])){
            throw new InvalidArgumentException('Invalid response format:'. $format);
        }

        if (!\in_array(\strtolower($type), ['base', 'all'])){
            throw new InvalidArgumentException('Invalid type value(live\forecast): '. $type);
        }

        // 2. 封装 query 函数，并对空值进行过滤。
        $query = array_filter([
                'key' => $this->key,
                'city' => $city,
                'output' => $format,
                'extensions' =>  $type,
        ]);

        try{
            // 3. 调用 getHttpClient 获取实例，并调用该实例的 'get' 方法
            //传递参数为两个：$url, ['query' => $query];
            $response = $this->getHttpClient()->get($url,[
                    'query' => $query,
             ])->getBody()->getContents();

            // 4. 返回值根据 $format 返回不同格式，
            // 当为 'json' 时返回数组格式，否则位 'xml'
            return 'json' === $format ? \json_decode($response, true) : $response;
        } catch (\Exception $e) {
            // 5. 当消息出现异常时捕获并抛出，消息为抛出的异常消息
            // 并将调用异常作为 $previousException 传入。
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }


    //获取实时天气方法
    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'base', $format);
    }

    //获取天气预报

    public function getForecastWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'all', $format);
    }



}
