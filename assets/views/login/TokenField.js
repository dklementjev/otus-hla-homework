import {Backbone} from "backbone_es6";

class TokenField extends Backbone.View{
    /**
     * @param {Auth} options.auth
     */
    initialize (options) {
        this._auth = options.auth;
        this.setupEvents();
    }

    setupEvents () {
        const _runUpdate = () => Promise.resolve().then(() => this.update());

        this.on("rendered", _runUpdate);
        this._auth.on("change", _runUpdate)
    }

    render () {
        this.trigger("rendered");
    }

    update () {
        this.$el.val(this._auth.token);
    }
}

export {TokenField};
