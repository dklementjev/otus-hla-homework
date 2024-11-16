import {Backbone} from "backbone_es6";

class PostsPage {
    /**
     * @param {jQuery} el
     */
    constructor(el) {
        this._el = el;
    }

    render () {
        this.hide();
        this.trigger("rendered");
    }

    show () {
        this._el.show();
    }

    hide () {
        this._el.hide();
    }
}
Object.assign(PostsPage.prototype, Backbone.Events);

export {PostsPage};
