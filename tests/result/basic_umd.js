(function (global, factory) {
    typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() :
        typeof define === 'function' && define.amd ? define(factory) :
            typeof global.vuei18nLocales === 'undefined' ? global.vuei18nLocales = factory() : Object.keys(factory()).forEach(function (key) {global.vuei18nLocales[key] = factory()[key]});
}(this, (function () { 'use strict';
    return {
    "en": {
        "help": {
            "yes": "yes",
            "no": "no"
        }
    },
    "sv": {
        "help": {
            "yes": "ja",
            "no": "nej"
        }
    }
}

})));