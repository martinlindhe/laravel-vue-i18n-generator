## About
[![Build Status](https://travis-ci.org/martinlindhe/laravel-vue-i18n-generator.png?branch=master)](https://travis-ci.org/martinlindhe/laravel-vue-i18n-generator)


Laravel 5 package that allows you to share your [Laravel localizations](http://laravel.com/docs/5.1/localization)
with your [vue](http://vuejs.org/) front-end, using [vue-i18n](https://github.com/kazupon/vue-i18n) or [vuex-i18n](https://github.com/dkfbasel/vuex-i18n).


## Install the package

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

## Using vue-i18n

Next, you need to install one out of two supported VueJs i18n libraries. We support [vue-i18n](https://github.com/kazupon/vue-i18n) as default library. Beside that we also support [vuex-i18n](https://github.com/dkfbasel/vuex-i18n).

When you go with the default option, you only need to install the library through your favorite package manager.
### vue-i18n
```
npm i --save vue-i18n
```

```
yarn add vue-i18n
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
## Using vuex-i18n
 
### vuex-i18n
```
npm -i --save vuex-i18n
```

```
yarn add vuex-i18n vuex
```

Next, open `config/vue-i18n-generator.php` and do the following changes:

```diff
- 'i18nLib' => 'vue-i18n',
+ 'i18nLib' => 'vuex-i18n',
```

Then generate the include file with
```
php artisan vue-i18n:generate
```

Assuming you are using a recent version of vuex-i18n, adjust your vue app with something like:
```js
import Vuex from 'vuex';
import vuexI18n from 'vuex-i18n';
import Locales from './vue-i18n-locales.generated.js';

const store = new Vuex.Store();

Vue.use(vuexI18n.plugin, store);

Vue.i18n.add('en', Locales.en);
Vue.i18n.add('de', Locales.de);

// set the start locale to use
Vue.i18n.set(Spark.locale);

require('./components/bootstrap');

var app = new Vue({
    store,
    mixins: [require('spark')]
});
```

## UMD module

If you want to generate an UMD style export, you can with the `--umd` option
```
php artisan vue-i18n:generate --umd
```
An UMD module can be imported into the browser, build system, node and etc. 

Now you can include the generated script in the browser as a normal script and reference it with window.vuei18nLocales.
```vue
<script src="{{ asset('js/vue-i18n-locales.generated.js') }}"></script>

// in your js 
Vue.use(VueI18n)
Vue.config.lang = Laravel.language
Object.keys(window.vuei18nLocales).forEach(function (lang) {
  Vue.locale(lang, window.vuei18nLocales[lang])
})
```
You can still require/import it in your build system as stated above.

One advantage of doing things like this is you are not obligated to do a build of your javascript each time a the translation files get changed/saved. A good example is if you have a backend that can read and write to your translation files (like Backpack). You can listen to a save event there and call vue-i18n-generator.


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

- The generated file is an ES6 module.

The more sophisticated pluralization localization as described [here](https://laravel.com/docs/5.5/localization#pluralization) is not supported since neither vue-i18n or vuex-i18n support this.

# License

Under [MIT](LICENSE)
