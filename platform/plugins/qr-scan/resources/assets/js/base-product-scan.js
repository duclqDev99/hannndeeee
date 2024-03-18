import Scanner from "./library/scanner";
import ProductService from "./services/product-service";

export default class BaseProductScan extends Scanner {
    constructor() {
        super();
        this.isFetching = false;
        this.callbackScan = null;
        this.callBackFetching = null;
        this.callBackError = null;

        super._init();
        super.onScan(this._postQrCode)
    }

    async _postQrCode(code) {
        if (typeof this.callbackScan === "function") this.callbackScan(code);

        try {
            this.isFetching = true;
            const res = await ProductService.postQrScan(code);
            if (res) {
                if (typeof this.callBackFetching === "function") this.callBackFetching(res);
                this.isFetching = false;
            }
        } catch(err) {
            if (typeof this.callBackError === "function") this.callBackError(err);
            this.isFetching = false;
        }
    }

    onScan(callback) {
        this.callbackScan = callback;
        return this;
    }

    onFetching(callback) {
        this.callBackFetching = callback;
        return this;
    }

    onScanError(callback) {
        this.callBackError = callback;
        return this;
    }
     
    start() {
        super.start();
        return this;
    }

    stop() {
        super.stop();
        return this;
    }
}