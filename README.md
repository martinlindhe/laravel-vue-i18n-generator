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
php artisan vendor:publish --provider="MartinLindhe\VueInternationalizationGenerator\GeneratorProvider"
```

Then generate the include file with
```
php artisan vue-i18n:generate
```

Assuming you are using a recent version of vue-i18n, adjust your vue app with something like:
```js
import Vue from 'vue';
import VueInternalization from 'vue-i18n';
import Locales from './vue-i18n-locales.generated.js';

Vue.use(VueInternalization);

Vue.config.lang = 'en';

Object.keys(Locales).forEach(function (lang) {
  Vue.locale(lang, Locales[lang])
});

...
```


For older vue-i18n, the initialization looks something like:

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
