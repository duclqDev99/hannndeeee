export default class Scanner {
    constructor() {
        this.codes = [];
        this.interval = null;
        this.keyCodeMap = {
            'Digit0': '0',
            'Digit1': '1',
            'Digit2': '2',
            'Digit3': '3',
            'Digit4': '4',
            'Digit5': '5',
            'Digit6': '6',
            'Digit7': '7',
            'Digit8': '8',
            'Digit9': '9',
            'KeyA': 'a',
            'KeyB': 'b',
            'KeyC': 'c',
            'KeyD': 'd',
            'KeyE': 'e',
            'KeyF': 'f',
            'KeyG': 'g',
            'KeyH': 'h',
            'KeyI': 'i',
            'KeyJ': 'j',
            'KeyK': 'k',
            'KeyL': 'l',
            'KeyM': 'm',
            'KeyN': 'n',
            'KeyO': 'o',
            'KeyP': 'p',
            'KeyQ': 'q',
            'KeyR': 'r',
            'KeyS': 's',
            'KeyT': 't',
            'KeyU': 'u',
            'KeyV': 'v',
            'KeyW': 'w',
            'KeyX': 'x',
            'KeyY': 'y',
            'KeyZ': 'z',
            'NumpadMultiply': '*',
            'NumpadAdd': '+',
            'NumpadSubtract': '-',
            'NumpadDecimal': '.',
            'NumpadDivide': '/',
            'Equal': '=',
            'Comma': ',',
            'Minus': '-',
            'Period': '.',
            'Slash': '/',
            'Backquote': '`',
            'BracketLeft': '[',
            'Backslash': `\\`,
            'BracketRight': ']',
            'Quote': `'`,
            'Enter': 'Enter',
            'ShiftLeft': 'Shift',
            'ShiftRight': 'Shift',
        };
        this.specialCharacters = [
            { unshifted: '`', shifted: '~', },
            { unshifted: '1', shifted: '!', },
            { unshifted: '2', shifted: '@', },
            { unshifted: '3', shifted: '#', },
            { unshifted: '4', shifted: '$', },
            { unshifted: '5', shifted: '%', },
            { unshifted: '6', shifted: '^', },
            { unshifted: '7', shifted: '&', },
            { unshifted: '8', shifted: '*', },
            { unshifted: '9', shifted: '(', },
            { unshifted: '0', shifted: ')', },
            { unshifted: '-', shifted: '_', },
            { unshifted: '=', shifted: '+', },
            { unshifted: '[', shifted: '{', },
            { unshifted: ']', shifted: '}', },
            { unshifted: '\\', shifted: '|', },
            { unshifted: ';', shifted: ':', },
            { unshifted: "'", shifted: '"', },
            { unshifted: ',', shifted: '<', },
            { unshifted: '.', shifted: '>', },
            { unshifted: '/', shifted: '?', }
        ];
        this.callbackOnScan = () => { };
        this._init()
    }

    _init() {
        if (!this.isEventBound) {
            this._scanHandler = this._scanProcessing.bind(this);
            this._pasteHandler = this._paste.bind(this);

            document.addEventListener('keyup', this._scanHandler);
            document.addEventListener('paste', this._pasteHandler);

            this.isEventBound = true;
        }
    }

    test(){
        console.log('asdsad')
    }
    start() {
        this.stop();
        this._init();
    }

    stop() {
        if (this.isEventBound) {
            document.removeEventListener('keyup', this._scanHandler);
            document.removeEventListener('paste', this._pasteHandler);
            this.isEventBound = false;
        }
    }

    onScan(callback) {
        this.callbackOnScan = callback;
    }

    _scanProcessing(event) {
        if (this.interval) clearInterval(this.interval);

        if (event.key == 'Enter') {
            if (typeof this.callbackOnScan == 'function') {
                let keyMappers = []
                this.codes.forEach((code, index) => {
                    if (code != '') {
                        keyMappers.push(this.keyCodeMap[code])
                    }
                });

                keyMappers = keyMappers.filter(char => char != undefined);
                let keyMappersFilter = keyMappers.filter((char, index) => {
                    if (char != 'Shift') return true;
                    else return char != keyMappers[index - 1];
                });

                keyMappersFilter.forEach((key, index) => {
                    if (key == 'Shift') {
                        const hasCombinationCharacter = this.specialCharacters.map(c => c.unshifted).includes(keyMappersFilter[index + 1]);
                        if (hasCombinationCharacter) {
                            const shiftCharacter = this.specialCharacters.find(item => item.unshifted == keyMappersFilter[index + 1]);
                            keyMappersFilter[index + 1] = shiftCharacter.shifted;
                        }
                        else keyMappersFilter[index + 1] = keyMappersFilter[index + 1].toUpperCase();
                    }
                });

                const code = keyMappersFilter.filter(item => item != 'Shift' && item != 'Enter').toString().split(",").join("");
                this.callbackOnScan(code);
            }
            return this.codes = [];
        }

        this.codes.push(event.code)
        this.interval = setInterval(() => {
            this.codes = [];
        }, 20);
    }

    _paste(event) {
        const pastedValue = (event.clipboardData || window.clipboardData).getData('text');
        this.callbackOnScan(pastedValue);
    }

    _preventDefaultZoomChrome(event) {
        if (event.key == '+' || event.key == '-') event.preventDefault();
    }
}