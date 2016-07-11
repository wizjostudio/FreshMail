FreshMail
=========

A PHP library that easies usage of FreshMail REST API 

Installation
============

The easiest way to get install the library is by using composer:

`composer require wizjo/freshmail`

Basic usage
===========

```php
$fm = new \Wizjo\FreshMail(API_KEY, API_SECRET);

//get request
$action = $fm->request('/ping');
$data = $action->getData();
echo $data['data']; //will print "pong"

//post request
$action = $fm->request('/ping', ['test' => 'data']);
$data = $action->getData();
echo $data['data']['test']; //will print "data"
```
