Cloudflare Actions
==================

Компонент Yii2 для работы с api v4.0 сервиса https://www.cloudflare.com/

Минимальные требования — Yii2

Допустимые ключи массива настроек (в скобках значения по-умолчанию):

```php
'components' => [
        'cloudflareactions' => [
            'class'         => 'integready\cloudflare\CloudflareActions',
            'apiendpoint'   => 'https://api.cloudflare.com/client/v4/',
            'authkey'       => '1qaz2wsx3edc4rfv5tgb6yhn7ujm8ik1qaz2w',
            'authemail'     => 'admin@mail.com',
            'sites'         => [
                'mysite.com',
                'anothersite.eu',
                'anotheronesite.biz',
            ],
        ],
]
```

***

Лицензия: LGPL v3 or later
