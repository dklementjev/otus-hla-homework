import {Backbone} from "backbone_es6";

/**
 * @class
 * @extends {Backbone.Model}
 */
const Post = Backbone.Model.extend({
    getUUID: function () {
        return this.get("uuid");
    },

    getAuthorUUID: function () {
        return this.get("authorUUID");
    },

    getText: function () {
        return this.get("text");
    }
});

export {Post};
