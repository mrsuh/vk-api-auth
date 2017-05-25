# vk api auth

## Installation

First you must create your application at vk.com's [applications page] (https://vk.com/apps?act=manage)

```bash
composer require mrsuh/vk-api-auth:1.*
```

## Usage

``` php
<?php
$params = [
    'app_id' =>  58384343// application id
     'username' => 'mrsuh6@gmail.com'// username
     'password' => '1Gw738hfud9828hf3XbSrQ3'// password
     'scope' => ['video', 'friends', 'messages']// list of permissions
];

$auth = new Mrsuh\Service\AuthService($params);
$token = $auth->getToken();

//make requests with token
```

helpful links:
* [application page] (https://vk.com/apps?act=manage)
* [permissions] (https://vk.com/dev/permissions)
* [error codes] (https://vk.com/dev/errors)
* [api methods] (https://vk.com/dev/methods)