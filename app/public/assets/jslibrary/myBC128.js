
var bc128b = {
    keys: ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~',
    startCode: 104,
    startCodeAsci: 204,
    startCodeChar: String.fromCharCode(204), // Ì
    stopCode: 106,
    stopCodeAsci: 206,
    stopCodeChar: String.fromCharCode(206), // Î

    startHogiChar: String.fromCharCode(209), // Ñ
    stopHogiChar: String.fromCharCode(211), // Ó

    keys_func: {},
    aCodeToAsci: {},

    getCharByCode: function(codeVal) {
        if (codeVal < 95) {
            return this.keys.charAt( codeVal );
        } else if (codeVal < 107) {
            if (!this.keys_func[95]) {
                for(var i = 95; i < 107; i++) this.keys_func[i] = String.fromCharCode(i+100);
            }
            return this.keys_func[ codeVal ];
        }
    },
    intiCodeToAsci: function() {
        var i, len = this.keys.length;
        for(i = 0; i < len; i++) {
            this.aCodeToAsci[i] = this.keys[i].charCodeAt(0);
        }
        for(i = 95; i < 107; i++) {
            this.aCodeToAsci[i] = i + 100;
        }
    },
    getCheckCode: function(barcode)
    {
        var i=-1, bcOrd = '',
            pruefSum = 0,
            len = barcode.length;

        for(i = 0; i < len; i++) {
            bcOrd = this.keys.indexOf( barcode.charAt(i) );
            if (bcOrd === -1) {
                error_log('Ungültiges Zeichen ' + barcode.charAt(i) + '(ASCI-Code: ' + barcode.charCodeAt(i) +')  im Barcode: ' + barcode + '!');
                return '';
            }
            pruefSum+= (i+1) * bcOrd;
        }

        var re = (this.startCode + pruefSum) % 103;
        return (this.startCode + pruefSum) % 103;
    },
    get: function(barcodeNr) {
        var pruefSum = this.getCheckCode(barcodeNr);

        var pruefOrdChar = this.getCharByCode( pruefSum );

        return this.startCodeChar + barcodeNr + pruefOrdChar + this.stopCodeChar;
    },
    hogi: function(barcodeNr) {
        var pruefSum = this.getCheckCode(barcodeNr);

        var pruefOrdChar = this.getCharByCode( pruefSum );

        return this.startHogiChar + barcodeNr + pruefOrdChar + this.stopHogiChar;
    }
};
