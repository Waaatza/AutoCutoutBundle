pimcore.registerNS("pimcore.plugin.WatzaAutoCutoutBundle");

pimcore.plugin.WatzaAutoCutoutBundle = Class.create({

    initialize: function () {
        document.addEventListener(pimcore.events.pimcoreReady, this.pimcoreReady.bind(this));
    },

    pimcoreReady: function (e) {
        // alert("WatzaAutoCutoutBundle ready!");
    }
});

var WatzaAutoCutoutBundlePlugin = new pimcore.plugin.WatzaAutoCutoutBundle();
