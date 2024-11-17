import {Backbone} from "backbone_es6";

/**
 * @class
 * @extends {Backbone.Model}
 */
const Post = Backbone.Model.extend({
    idAttribute: "uuid",

    getId: function () {
        return this.get("id");
    },

    getAuthorId: function () {
        return this.get("author_user_id");
    },

    getText: function () {
        return this.get("text");
    }
});

export {Post};
