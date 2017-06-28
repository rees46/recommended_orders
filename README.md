# Учёт заказов с рекомендованнными товарами в A/B-тестировании #

`php 5.5+`
 
## Назначение ## 

Проведение A/B-тестов, учитывающих для выявления победителя только заказы с рекомендованными товарами.

Методы библиотеки должны вызываться в момент добавления товара в корзину из блока рекомендаций, в момент удаления товара из корзины, а также в момент оформления заказа.

Массив ID рекомендованных товаров сохраняется в Cookie и его целостность проверяется контрольным значением (md5 hash) в дополнительной Cookie, защищённой от изменений в JavaScript.




## Установка ##

Положить файл RecommendedOrders.php в папку с вашим проектом и подключить его.

## Использование ##

1. Методы библиотеки

```php
<?php

// Инициализировать библиотеку со заданным именем Cookie
$recOrders = new RecommendedOrders('MyCookieName');

// Добавление ID рекомендованного товара в массив
// @return true|false
$result = $recOrders->addID(123890);
$result = $recOrders->addID('SPB12345');

// Удаление ID товара из массива или полная очистка массива
// @return true|false
$result = $recOrders->removeID(123890);
$result = $recOrders->removeID('SPB12345');
$result = $recOrders->removeID(); // полная очистка массива

// Проверить наличие товаров в массиве и состояние массива по контрольной сумме
// @return true|false
$result = $recOrders->isRecommended();
$result = $recOrders->isRecommended(false); // не удалять Cookie с массивом

?>

```

2. Пример получение данных с frontend

```php
<?php

    // '/backend/recOrders.php'

    $recOrders = new RecommendedOrders('MyCookieName');
    $event = !empty($_REQUEST['event']) ? strtolower($_REQUEST['event']) : null;
    $param = !empty($_REQUEST['param']) ? $_REQUEST['param'] : null;

    switch ($event) {
        case 'add':
            $result = $recOrders->addID($param);
            break;
        case 'remove':
            $result = $recOrders->removeID($param);
            break;
        case 'check':
            $result = $recOrders->isRecommended(false); // не удаляет Cookie с массивом товаров, см. комментарии ниже
            break;
        case 'check_last':
            $result = $recOrders->isRecommended(); // удаляет Cookie с массивом товаров, см. комментарии ниже
            break;
        default:
            $result = false;
    }
    echo $result ? json_encode(['status' => 'success']) : json_encode(['status' => 'unsuccess']);

?>
```

3. Использование в шаблонах:

3.1. Просмотр страницы товара

```html

<!-- В момент отправки в рекомендательную систему JS-трекинга просмотра страницы рекомендованного товара  -->
<script>
r46('track', 'view', 100500); // трекинг REES46 для события "просмотра товара"

if (typeof recommendedType != 'undefined' && recommendedType) { // "recommendedType" или любая другая переменная, идентифицирующая товар как рекомендованный
    jQuery.ajax({
        url: '/backend/recOrders.php',
        method: 'POST',
        data: {event: 'add', param: 100500}, // "param" содержит ID рекомендованного товара
        dataType: 'json',
        success: function(e){
            // если запрос успешен
        },
        error: function(a, b, c){
            // если запрос завершился ошибкой
        }
    });
}
</script>

```

3.2. Добавление товара в корзину

```html

<!-- В момент отправки в рекомендательную систему JS-трекинга добавления рекомендованного товара в корзину  -->
<script>
r46('track', 'cart', { // трекинг REES46 для события "добавил рекомендованный товар в корзину"
    id: 100500, 
    amount: 3, 
    stock: true, 
    recommended_by: recommendedType // "recommendedType" или любая другая переменная, идентифицирующая товар как рекомендованный
}); 

if (typeof recommendedType != 'undefined' && recommendedType) { // "recommendedType" или любая другая переменная, идентифицирующая товар как рекомендованный
    jQuery.ajax({
        url: '/backend/recOrders.php',
        method: 'POST',
        data: {event: 'add', param: 100500}, // "param" содержит ID рекомендованного товара
        dataType: 'json',
        success: function(e){
            // если запрос успешен
        },
        error: function(a, b, c){
            // если запрос завершился ошибкой
        }
    });
}
</script>
```

3.3. Удаление любого товара из корзины

```html

<!-- В момент отправки в рекомендательную систему JS-трекинга удаления любого товара из корзины  -->
<script>
r46('track', 'remove_from_cart', 100500); // трекинг REES46 для события "удаления товара из корзины"

jQuery.ajax({
    url: '/backend/recOrders.php',
    method: 'POST',
    data: {event: 'remove', param: 100500}, // "param" содержит ID удаляемого товара
    dataType: 'json',
    success: function(e){
        // если запрос успешен
    },
    error: function(a, b, c){
        // если запрос завершился ошибкой
    }
});
</script>
```

3.4. Полная очистка корзины

```html

<!-- В момент отправки в рекомендательную систему JS-трекинга очистки корзины  -->
<script>
r46('track', 'cart', []); // трекинг REES46 для полной очистки корзины

jQuery.ajax({
    url: '/backend/recOrders.php',
    method: 'POST',
    data: {event: 'remove'}, 
    dataType: 'json',
    success: function(e){
        // если запрос успешен
    },
    error: function(a, b, c){
        // если запрос завершился ошибкой
    }
});
</script>
```

3.5. Финальная страница оформления заказа

```html

<!-- В момент отправки в рекомендательную систему JS-трекинга оформления заказа  -->
<script>
r46('track', 'purchase', { // трекинг REES46 для события оформления заказа
    products:[
        {id: 100500, price: 7999, amount: 1},
        {id: 123456, price: 2500, amount: 2},
    ],
    order: 112233,
    order_price: 12999
}); 

var isRecommended;
jQuery.ajax({
    url: '/backend/recOrders.php',
    method: 'POST',
    data: {event: 'check'}, // 'check' не удаляет Cookie с массивом рекомендованных товаров
                            // 'check_last' удаляет Cookie с массивом рекомендованных товаров
                            // Если оригинальный заказ разбивается на несколько заказов, например на 
                            // товары в наличии и на товары под заказ, то для всех событий заказа
                            // кроме последнего необходимо вызывать 'check', а для последнего 'check_last'.
    dataType: 'json',
    success: function(e){
        isRecommended = e.hasOwnProperty('status') && e.status == 'success' ? true : false;

        // отправка данных заказа в Google Analytics
        ga('require', 'ec');    // Для передачи в Google Analytics дополнительных параметров заказа
                                // необходимо использовать расширенную электронную торговлю Google -
                                // https://developers.google.com/analytics/devguides/collection/analyticsjs/enhanced-ecommerce
        ga('ec:setAction', 'purchase', {
            'id': '112233',
            'affiliation': 'Online Store',
            'revenue': '12999',
            'tax': '0',
            'shipping': '0',
            'option': isRecommended ? 'recommendedOrder' : '' // значение 'recommendedOrder' или любое другой, маркирующего заказ с рекомендованными товарами
        });
        ga('send', 'pageview');  
    },
    error: function(){
        // если запрос завершился ошибкой
    }
});
</script>

```
