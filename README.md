# php-utils

> Some useful classes that I use a lot in my PHP projects.

## App/CacheFactory

Cache objects into disk or memcache/d.

### App/CacheFactory/File

File backend.

### App/ConfigFactory/Memcache

Memcache backend.

### App/ConfigFactory/Memcached

Memcached backend.

## App/ConfigFactory

Factory to read some formats of configuration.

### App/ConfigFactory/Json

Read JSON files.

### App/ConfigFactory/Php

Read PHP files.

## App/Crypto

Encrypts and decrypts plaintext with a given key.  Written to be compatible with it's counterpart in js. It use AES. Uses the slowAES encryption lib, which is more than fast enough for our purposes; using it here because it has several parallel versions in different languages (mainly php and js).

## App/Db/Mysql

mysql_* functions wrapper.

## App/Db/Mysqli

mysqli_* functions wrapper.

## App/Filter/Input

To filter inputs.

## App/Form

HTML form creation and validation.

### App/Form/Element/Checkbox

### App/Form/Element/Radio

### App/Form/Element/Select

### App/Form/Element/Text

## App/Hmac

HMAC implementation.

## App/Mailer

To send emails as html or plain text.

## App/Mailer

To send emails as html or plain text.

## App/Util/Array

Array utilities.

## App/Util/Text

Text utilities.

## App/Video/Converter

Convert videos with mobile support, or any other format with ffmpeg.

## App/Video/Downloader

Download videos from YouTube, Vimeo or any other embed URL.

## App/WidgetFactory

A widgets factory.
