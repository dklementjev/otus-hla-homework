import {Backbone} from "backbone_es6";
import {template} from "underscore";

/**
 * @class
 * @extends {Backbone.View}
 */
const Post = Backbone.View.extend({
    initialize: function () {
        if (!this.model) {
            throw new Error("Model is empty");
        }
        this.template = template(
            '<div class="card my-2"><div class="card-body">' +
            '<p class="card-text"><%- data.text %></p>'+
            '<p class="text-secondary fs-6">' +
                '<strong>id:</strong> <%= data.id %><br/>'+
                '<strong>author:</strong> <%= data.author %>'+
            '</p>'+
            '</div></div>',
            {variable: "data"}
        );
    },

    render: function () {
        this.$el.html(
            this.template(this.getTemplateData())
        );
        this.trigger("rendered");
    },

    getEl: function () {
        return this.$el;
    },

    getTemplateData: function () {
        return {
            id: this.model.getId(),
            author: this.model.getAuthorId(),
            text: this.model.getText()
        };
    }
});

export {Post}
