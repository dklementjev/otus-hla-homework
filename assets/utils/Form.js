class Form {
    constructor (name) {
        this._formName = name;
    }

    getEl () {
        return $(`form[name=${this._formName}]`);
    }

    getField (fieldName) {
        return this.getEl().find(`[name=${this._formName}\\[${fieldName}\\]]`);
    }
}

export {Form};
