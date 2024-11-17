import {Backbone} from "backbone_es6";

/**
 * @extends {Backbone.Events}
 */
class SidebarBlock {
    /**
     * @param {jQuery} el
     */
    constructor(el) {
        this._el = el;
        this.setupEvents();
    }

    setupEvents () {
        this._el.find("li a").on("click", (e) => this.clickHandler(e))
    }

    clickHandler (e) {
        const el = $(e.target),
            action = el.data("action")
        ;

        if (action) {
            this.trigger("action", {name: action});
        }
    }

    render() {
        this.trigger("rendered");
    }
}
Object.assign(SidebarBlock.prototype, Backbone.Events);

export {SidebarBlock};
