<h1 align="center"> weather </h1>

<p align="center">基于 <a href="https://lbs.amap.com">高德开放平台</a> 的 PHP 天气信息组件。
                  
</p>



## Installing

```shell
$ composer require overtest/weather -vvv
```

## 配置

在使用本扩展之前，你需要去 <a href="https://lbs.amap.com">高德开放平台</a> 注册账号，然后创建应用，获取应用的 API Key。

## 使用
````
use overtest\Weather\Weather;

$key = 'xxxxxxxxxxxxxxxxxxxxxxxxxxx';

$weather = new Weather($key);
````
#### 获取实时天气
````
$response = $weather->getWeather('深圳');

示例：
{
    "status": "1",
    "count": "1",
    "info": "OK",
    "infocode": "10000",
    "lives": [
        {
            "province": "广东",
            "city": "深圳市",
            "adcode": "440300",
            "weather": "中雨",
            "temperature": "27",
            "winddirection": "西南",
            "windpower": "5",
            "humidity": "94",
            "reporttime": "2018-08-21 16:00:00"
        }
    ]
}
````
#### 获取近期天气预报
``````
$response = $weather->getWeather('深圳'，'all');
``````
示例：
``````
{
    "status": "1", 
    "count": "1", 
    "info": "OK", 
    "infocode": "10000", 
    "forecasts": [
        {
            "city": "深圳市", 
            "adcode": "440300", 
            "province": "广东", 
            "reporttime": "2018-08-21 11:00:00", 
            "casts": [
                {
                    "date": "2018-08-21", 
                    "week": "2", 
                    "dayweather": "雷阵雨", 
                    "nightweather": "雷阵雨", 
                    "daytemp": "31", 
                    "nighttemp": "26", 
                    "daywind": "无风向", 
                    "nightwind": "无风向", 
                    "daypower": "≤3", 
                    "nightpower": "≤3"
                }, 
                {
                    "date": "2018-08-22", 
                    "week": "3", 
                    "dayweather": "雷阵雨", 
                    "nightweather": "雷阵雨", 
                    "daytemp": "32", 
                    "nighttemp": "27", 
                    "daywind": "无风向", 
                    "nightwind": "无风向", 
                    "daypower": "≤3", 
                    "nightpower": "≤3"
                }, 
                {
                    "date": "2018-08-23", 
                    "week": "4", 
                    "dayweather": "雷阵雨", 
                    "nightweather": "雷阵雨", 
                    "daytemp": "32", 
                    "nighttemp": "26", 
                    "daywind": "无风向", 
                    "nightwind": "无风向", 
                    "daypower": "≤3", 
                    "nightpower": "≤3"
                }, 
                {
                    "date": "2018-08-24", 
                    "week": "5", 
                    "dayweather": "雷阵雨", 
                    "nightweather": "雷阵雨", 
                    "daytemp": "31", 
                    "nighttemp": "26", 
                    "daywind": "无风向", 
                    "nightwind": "无风向", 
                    "daypower": "≤3", 
                    "nightpower": "≤3"
                }
            ]
        }
    ]
}
``````
#### 获取 XML 格式返回值
第三个参数为返回值类型， 可选 `json` 与 `xml` , 默认 `json` :
```
$response = $weather->getWeather('深圳','all','xml');
```

示例：
``````
<response>
    <status>1</status>
    <count>1</count>
    <info>OK</info>
    <infocode>10000</infocode>
    <lives type="list">
        <live>
            <province>广东</province>
            <city>深圳市</city>
            <adcode>440300</adcode>
            <weather>中雨</weather>
            <temperature>27</temperature>
            <winddirection>西南</winddirection>
            <windpower>5</windpower>
            <humidity>94</humidity>
            <reporttime>2018-08-21 16:00:00</reporttime>
        </live>
    </lives>
</response>
``````

## 参数说明
``````
array|string getWeather(string $city, string $type='base', string $format='json')
``````

* `$city` - 城市名，比如：深圳；
* `$type` - 返回内容类型：`base` 返回实况天气， `all` 返回天气预报；
* `$format` - 输出数据类型，默认时 `json` 格式，当 `output` 设置为“`xml` ”，输出的为 XML 格式的数据。

## 在 Laravel 中使用

在 Laravel 中使用也是同样的安装方式，配置写在 config/services.php 中：
```$xslt
    .
    .
    .
     'weather' => [
        'key' => env('WEATHER_API_KEY'),
    ],
```
然后在 .env 中配置 WEATHER_API_KEY ：
```$xslt
WEATHER_API_KEY=xxxxxxxxxxxxxxxxxxxxx
```

可以用两种方式来获取 overtest\Weather\Weather 实例：

#### 方法参数注入

``` .
       .
       .
       public function edit(Weather $weather) 
       {
           $response = $weather->getWeather('深圳');
       }
       .
       .
       .
       
  ```

#### 服务名访问
```$xslt
    .
    .
    .
    public function edit() 
    {
        $response = app('weather')->getWeather('深圳');
    }
    .
    .
    .
```

## 代码重构

#### 重新设计参数
就 $type 参数而言，base 代表实时，all 代表预报本来就不是特别合理的设计，不直接对用户暴露接口参数，重新设计合理方便阅读的参数对外使用，将设计的参数与接口参数作对应，比如，我们可以改成下面这样子：
src/Weather.php
```
 .
    public function getWeather($city, $type = 'live', $format = 'json')
    {
        $url = 'https://restapi.amap.com/v3/weather/weatherInfo';

        $types = [
            'live' => 'base',
            'forecast' => 'all',
        ];

        if (!\in_array(\strtolower($format), ['xml', 'json'])) {
            throw new InvalidArgumentException('Invalid response format: '.$format);
        }

        if (!\array_key_exists(\strtolower($type), $types)) {
            throw new InvalidArgumentException('Invalid type value(live/forecast): '.$type);
        }
        .
        .
        .
    }
```

#### 添加语义化的方法

添加专用的语义化方法 来优化我们的代码，比如我们添加下面两个方法：

* `getLiveWeather` - 获取实时天气
* `getForecastsWeather` - 获取天气预报

src/Weather.php
```angular2html
    .
    .
    public function getLiveWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'base', $format);
    }

    public function getForecastsWeather($city, $format = 'json')
    {
        return $this->getWeather($city, 'all', $format);
    }
    .
    .
```


## 参考

* <a href="https://lbs.amap.com/api/webservice/guide/api/weatherinfo/">高德开放平台天气接口</a>

## License

MIT