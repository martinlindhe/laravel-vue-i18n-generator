## About
[![Build Status](https://travis-ci.org/martinlindhe/laravel-vue-i18n-generator.png?branch=master)](https://travis-ci.org/martinlindhe/laravel-vue-i18n-generator)


Laravel 5 package that allows you to share your [Laravel localizations](http://laravel.com/docs/5.1/localization)
with your [vue](http://vuejs.org/) front-end, using [vue-i18n](https://github.com/kazupon/vue-i18n).


## Usage

In your project:
```composer require martinlindhe/laravel-vue-i18n-generator```

In ```config/app.php``` providers:

```php
MartinLindhe\VueInternationalizationGenerator\GeneratorProvider::class,
```

Next, publish the package default config:

```
php artisan vendor:publish
```

Then generate the include file with
```
php artisan vue-i18n:generate
```

Adjust your vue app with something like:

```js
import Vue from 'vue';
import VueInternationalization from 'vue-i18n';

import Locales from './vue-i18n-locales.generated.js';

Vue.use(VueInternationalization, {
    lang: 'en',
    locales: Locales
});

...
```

## Parameters

The generator adjusts the strings in order to work with vue-i18n's named formatting,
so you can reuse your Laravel translations with parameters.
 
resource/lang/message.php:
```php
return [
    'hello' => 'Hello :name',
];
```

in vue-i18n-locales.generated.js:
```js
...
    "hello": "Hello {name}",
...
```

Blade template:
```php
<div class="message">
    <p>{{ trans('message.hello', ['name' => 'visitor']) }}</p>
</div>
```

Vue template:
```js
<div class="message">
    <p>{{ $t('message.hello', {name: 'visitor'}) }}</p>
</div>
```


## Notices

The generated file is an ES6 module.

[Pluralization](http://laravel.com/docs/5.1/localization#pluralization) don't work with vue-i18n
