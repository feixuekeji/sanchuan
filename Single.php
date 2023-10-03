<?php
class Single
{

    protected $config;

    public function __construct()
    {
        // 从 config.env 文件中读取配置信息
        $this->config = parse_ini_file('config.env');
    }

    public function open($name,$status)
    {
        if(empty($name))
            return '不能为空';
        // 获取开关信息接口
        $controlSwitchUrl = $this->config['api'] . 'stu/workerman/single';
        $accessToken = $this->getAccessToken();
        $headers = array(
            'Authorization: Bearer ' . $accessToken
        );

        $switchData = $this->getSingle();
        // 控制开关接口

        $key = array_search($name, array_column($switchData, "name"));
        if ($key !== false) {
            $channelId = $switchData[$key]['channel_id'];
        } else {
            return "未找到匹配的信息";
        }
        // 构造控制开关请求参数
        $controlData = array(
            'channel_id' => $channelId,
            'status' => $status
        );
        // 发送控制开关请求
        $controlSwitchResponse = $this->sendRequest($controlSwitchUrl, 'POST', $controlData, $headers);
        $this->log(json_encode($controlSwitchResponse));
        // 解析控制开关响应
        if (isset($controlSwitchResponse['msg'])) {
            $res['code'] = true;
            $res['msg'] = $name."控制结果: " . $controlSwitchResponse['msg'];
        } else{
            $res['code'] = false;
            $res['msg'] = json_encode($controlSwitchResponse);
        }
        return $res;
    }


    // 发送HTTP请求的函数

    private function getAccessToken()
    {
        // 文件缓存路径
        $cacheFilePath = 'access_token.cache';

        // 检查是否存在有效的 accessToken 缓存
        if (file_exists($cacheFilePath)) {
            $cache = unserialize(file_get_contents($cacheFilePath));

            $accessToken = $cache['access_token'];
            if ($cache['expires_time'] > time() && $this->refresh($accessToken))
                return $accessToken;


        }
        // 如果没有有效的 accessToken 缓存，进行登录认证
        $accessToken = $this->login();
        if ($accessToken) {
            // 缓存 accessToken
            $cache = $accessToken;
            $cache['expires_time'] = time() + $accessToken['expires_in'];
            file_put_contents($cacheFilePath, serialize($cache));
            return $accessToken;
        } else {
            echo "认证失败";
        }

    }

    public function login()
    {
        // 在这里实现登录认证并获取 accessToken 的逻辑
        // 使用 $config 中的配置信息发送登录请求，并解析响应获取 accessToken
        // 返回获取到的 accessToken 或者返回 false 如果认证失败
        // 你可以参考之前提供的示例代码
        // 注意要使用缓存的配置信息
        // 登录接口
        $loginUrl = $this->config['api'] . 'oauth/token';
        $loginData = array(
            'grant_type' => 'password',
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'username' => $this->config['username'],
            'password' => $this->config['password']
        );

    // 发送登录请求
        $loginResponse = $this->sendRequest($loginUrl, 'POST', $loginData);

    // 解析登录响应，获取Bearer Token
        $accessToken = isset($loginResponse['access_token']) ? $loginResponse : '';
        if (empty($accessToken)) {
            die("登录失败");
        }
        return $accessToken;
    }

    private function getSingle()
    {

        // 文件缓存路径
        $cacheFilePath = 'switch_data.cache';

        // 检查是否存在有效的 accessToken 缓存
        if (file_exists($cacheFilePath)) {
            $cache = unserialize(file_get_contents($cacheFilePath));
            $data = $cache['switch_data'];
            if ($cache['expires_time'] > time())
                return $data;
        }
        // 获取开关信息接口
        $getSwitchUrl = $this->config['api'] . 'stu/home/single';
        $accessToken = $this->getAccessToken();
        $headers = array(
            'Authorization: Bearer ' . $accessToken
        );
        $getSwitchResponse = $this->sendRequest($getSwitchUrl, 'POST', array(), $headers);
        // 解析获取开关信息响应
        $switchData = isset($getSwitchResponse['data']) ? $getSwitchResponse['data'] : '';
        $cache['switch_data'] = $switchData;
        $cache['expires_time'] = time() + 86400 * 15;
        file_put_contents($cacheFilePath, serialize($cache));
        return $switchData;

    }

    function sendRequest($url, $method, $data = array(), $headers = array())
    {
        $ch = curl_init();

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } elseif ($method === 'GET') {
            $url .= '?' . http_build_query($data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 设置 User-Agent 头信息
        $userAgent = "M2104K10AC(Android/13) (uni.suntrans.sanchuanzhishexuesheng/1.2.1) Weex/0.26.0 1080x2260"; // 替换成你希望设置的 User-Agent
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    function refresh($accessToken)
    {

        $url = $this->config['api'] . '/stu/home/refresh';
        $headers = array(
            'Authorization: Bearer ' . $accessToken
        );
        $getSwitchResponse = $this->sendRequest($url, 'POST', array(), $headers);
        // 解析获取开关信息响应
        $switchData = isset($getSwitchResponse['data']) ? $getSwitchResponse['data'] : 0;
        return $switchData;

    }

    function log($message)
    {
        // 将消息写入到文件
        $filePath = date("Y-m-d").".log";
        error_log("\n".date("Y-m-d H:i:s").$message, 3, $filePath);
        return true;

    }

}



