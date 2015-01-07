# php-utils

> Some useful classes that I use a lot in my PHP projects.

## Nebiros\PhpUtils\CacheFactory

Cache objects into disk or memcache\d.

### Nebiros\PhpUtils\CacheFactory\File

File backend.

### Nebiros\PhpUtils\ConfigFactory\Memcache

Memcache backend.

### Nebiros\PhpUtils\ConfigFactory\Memcached

Memcached backend.

## Nebiros\PhpUtils\ConfigFactory

Factory to read some formats of configuration.

### Nebiros\PhpUtils\ConfigFactory\Json

Read JSON files.

### Nebiros\PhpUtils\ConfigFactory\Php

Read PHP files.

## Nebiros\PhpUtils\Crypto

Encrypts and decrypts plaintext with a given key.  Written to be compatible with it's counterpart in js. It use AES. Uses the slowAES encryption lib, which is more than fast enough for our purposes; using it here because it has several parallel versions in different languages (mainly php and js).

## Nebiros\PhpUtils\Db\Mysql

mysql_* functions wrapper.

## Nebiros\PhpUtils\Db\Mysqli

mysqli_* functions wrapper.

## Nebiros\PhpUtils\Filter\Input

To filter inputs.

## Nebiros\PhpUtils\Form

HTML form creation and validation.

### Nebiros\PhpUtils\Form\Element\Checkbox

### Nebiros\PhpUtils\Form\Element\Radio

### Nebiros\PhpUtils\Form\Element\Select

### Nebiros\PhpUtils\Form\Element\Text

## Nebiros\PhpUtils\Hmac

HMAC implementation.

## Nebiros\PhpUtils\Mailer

To send emails as html or plain text.

## Nebiros\PhpUtils\Util\Arrays

Array utilities.

## Nebiros\PhpUtils\Util\Text

Text utilities.

## Nebiros\PhpUtils\Video\Converter

Convert videos with mobile support, or any other format with ffmpeg.

## Nebiros\PhpUtils\Video\Downloader

Download videos from YouTube, Vimeo or any other embed URL.

## Nebiros\PhpUtils\WidgetFactory

A widgets factory.
