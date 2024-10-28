class Container {
    constructor() {
        this._instances = new Map();
        this._factories = new Map();
    }

    has(id) {
        return this._factories.has(id);
    }

    /**
     * @param {String} id
     * @param {any|function (Container): any} factory
     *
     * @returns {Container}
     */
    set(id, factory) {
        if (this._factories.has(id)) {
            throw new Error(`Duplicate id: ${id}`);
        }
        this._factories.set(id, typeof(factory)==='function' ? factory : () => factory)

        return this;
    }

    get(id) {
        if (!this._instances.has(id)) {
            if (!this._factories.has(id)) {
                throw new Error(`Unknown id: ${id}`);
            }

            const factory = this._factories.get(id);
            this._instances.set(id, factory(this));
        }

        return this._instances.get(id);
    }

    /**
     * @returns {Container}
     */
    static getInstance() {
        if (!Container._self) {
            Container._self = new Container();
        }

        return Container._self;
    }
}

export {Container};
