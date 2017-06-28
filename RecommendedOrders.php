<?php

/*
* Created by REES46
* User: Andrey Veprikov
* Date: 28/06/2017
* Time: 14:30
* Библиотека создаёт, изменяет и проверяет две cookie, где в одной хранится массив JSON 
* с ID рекомендованных товаров, а во второй - md5-hash этого JSON.
* Cookie с hash защищена от изменений через JavaScript.
*/

class RecommendedOrders {

    /**
    * Постфикс для имени cookie с hash
    * @var string 
    */
    const POSTFIX = '_hash';

    /**
    * Время жизни cookie в днях
    * Cookie принудительно удаляется в методе isRecommended независимо от заданного значения
    * Не рекомендуется устанавливать маленькое значение, чтобы при возврате к брошенной корзине
    * спустя несколько дней товары в корзине всё ещё оставались рекомендованными
    * @var int 
    */
    private $cookie_expire = 14;

    /**
    * Текущий массив с рекомендованными ID
    * @var array
    */
    private $recommended_items;

    /**
    * Имя cookie
    * @var string
    */
    private $cookie_name;

    /**
    * @param string $name Имя cookie
    * @throws Exception
    */
    public function __construct($name = '') {
        if (empty($name)) {
            throw new Exception('Incorrect cookie\'s name');
        }
        $this->cookie_name = $name;
        $this->recommended_items = $this->readCookie();
    }

    
    /**
    * @return array
    */
    private function readCookie() {
        $json = $_COOKIE[$this->cookie_name];
        $json_hash = $_COOKIE[$this->cookie_name.self::POSTFIX];
        return (!empty($json) && !empty($json_hash) && $json_hash == md5($json)) ? json_decode($json, true) : [];
    }

    /**
    * @param boolean $set true/false - установить/удалить cookie, по-умолчанию true
    * @return true|false
    */
    private function writeCookie($set = true) {
        $value = (!$set || empty($this->recommended_items)) ? null : json_encode($this->recommended_items);
        $expire = (!$set || empty($this->recommended_items)) ? -1 : time()+60*60*24*$this->cookie_expire;
        if (setCookie($this->cookie_name, $value, $expire, '/', '', false, false)) {
            return $this->writeCookieHash($set);
        }
        return false;
    }

    /**
    * @return true|false
    */
    private function writeCookieHash($set) {
        $value = (!$set || empty($this->recommended_items)) ? null : md5(json_encode($this->recommended_items));
        $expire = (!$set || empty($this->recommended_items)) ? -1 : time()+60*60*24*$this->cookie_expire;
        return setCookie ($this->cookie_name.self::POSTFIX, $value, $expire, '/', '', false, true);
    }

    /**
    * @param int|string $id ID добавляемого рекомендованного товара
    * @return array
    */
    public function addID($id) {
        if (!empty($id) && (is_numeric($id) || is_string($id)) && !in_array((string)$id, $this->recommended_items)) {
            array_push($this->recommended_items, (string)$id);
        }
        return $this->writeCookie();
    }

    /**
    * @param int|string $id ID удаляемого товара
    * @return array
    */
    public function removeID($id = null) {
        if (is_null($id)) {
            $this->recommended_items = [];
        } elseif ((is_numeric($id) || is_string($id)) && in_array((string)$id, $this->recommended_items)) {
            unset($this->recommended_items[array_search((string)$id, $this->recommended_items)]);
        }
        return $this->writeCookie();
    }

    /**
    * @param boolean $remove_cookies true/false - удалять/не_удалять cookie, по-умолчанию true
    * @return true|false
    */
    public function isRecommended($remove_cookies = true) {
        $result = count($this->recommended_items) > 0 ? true : false;
        if ($remove_cookies) {
            $this->writeCookie(false);
        }
        return $result;
    }
}
