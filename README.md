## About

Laravel 5 package that allows you to share your Laravel translations
with your [vue](http://vuejs.org/) front-end, using [vue-i18n](https://github.com/kazupon/vue-i18n).


## Usage

In your project:
```composer require martinlindhe/laravel-vue-i18n-generator```

In ```config/app.php``` providers:

```php
MartinLindhe\VueInternationalizationGenerator\GeneratorProvider::class,
```

Then generate the include file with ```php artisan vue-i18n:generate```

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


## Notice

The generated file is an ES6 module.
