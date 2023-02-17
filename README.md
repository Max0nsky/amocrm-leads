<p align="center">
    <h1 align="center">Доступ к методам API AmoCRM - leads</h1>
    <br>
</p>

Текущий класс проекта AmoCRM.php предоставляет методы для взаимодействия с API v4 AmoCRM, а именно добавление сделок для воронок. Он позволяет пользователям аутентифицироваться, используя заранее полученные токены доступа, хранящиеся в файле JSON. Первичное получение access_token, refresh_token и end_token_time не представлено, для этого необходимо обратиться к <a href="https://www.amocrm.ru/developers/content/oauth/step-by-step" target="_blank"> официальной документации</a> сервиса.

<h2>Свойства класса</h2>
<ul>
 <li>accessToken - строка, содержащая токен доступа, полученный из AmoCRM API во время аутентификации</li>
 <li>baseUrl - строка, содержащая базовый URL для конечной точки AmoCRM API v4</li>
 <li>refreshTokenUpdateTime - целое число, содержащее временную метку, когда токен доступа истечет и потребуется обновление</li>
</ul>
<h2>Константы</h2>
<ul>
 <li>CLIENT_SECRET - строка-константа, представляющая секрет клиента, связанный с учетной записью AmoCRM.</li>
 <li>CLIENT_ID - строка-константа, представляющая идентификатор клиента, связанный с учетной записью AmoCRM.</li>
 <li>SUBDOMAIN - строка-константа, представляющая поддомен учетной записи AmoCRM.</li>
</ul>
<br>

Пример использования:
```php
include "AmoCRM.php";

$amoCrm = new AmoCRM();
$response = $amoCrm->addDeal("Новая сделка", 5000, "Телефон Xiaomi", "VENDORCODE");

var_dump($response);
```
