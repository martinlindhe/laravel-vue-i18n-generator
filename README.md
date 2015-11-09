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
var Vue = require('vue')
var i18n = require('vue-i18n')

import Locales from './vue-i18n-locales.generated.js';

Vue.use(i18n, {
    lang: 'en',
    locales: Locales
});

...
```


## Notice

The generated file is an ES6 module.
