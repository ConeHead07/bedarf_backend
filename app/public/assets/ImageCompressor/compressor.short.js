
var readingFiles = [];
var loadingImages = [];
var defaultConf = {
    maxSize: 50 * 1024,
    maxWidth: 800,
    maxHeight: 800,
    minQuality: .6,
    currSize: null,
    fileName: null
};

var MSG_FileSizeMustNotBeChanged = 'FileSizeMustNotBeChanged';

function getScaleFactor(srcWidth, srcHeight, maxWidth, maxHeight) {
    var w=srcWidth, h=srcHeight, mw=maxWidth, mh=maxHeight;
    return Math.min(1, (w > mw ? mw/w : 1), (h > mh ? mh/h : 1));
}

function fileSizeReadable(fsize, unit='auto', withUnit = true) {
    if (!unit) unit = 'auto';
    if ( (fsize < 1024 && unit === 'auto') || ['Bytes', 'B'].indexOf(unit) > -1) {
        return fsize + (withUnit ? ' Bytes' : '');
    } else if ((fsize < (1024 * 1024) && unit === 'auto') || unit === 'KB') {
        return (fsize / 1024).toFixed(1).toString().replace('.', ',') + (withUnit ? ' KB' : '');
    }
    return (fsize / (1024 * 1024)).toFixed(1).replace('.', ',') + (withUnit ? ' MB' : '');
}

function compress(inputFile, opts) {
    return new Promise(function(resolve, reject) {
        var settings = $.extend({}, opts, {
           maxSize: 0,
           maxWidth: 0,
           maxHeight: 0,
           minQuality: .6,
           currSize: inputFile.size,
           fileName: inputFile.name
        });

        loadImage(inputFile)
            .catch(function() {
                reject('ImageLoadError');
            })
            .then( function(img) {

                compressImageToFile(img, settings)
                    .catch( function(msg) {
                        if (msg === MSG_FileSizeMustNotBeChanged) {
                            reject(msg);
                        } else {
                            reject('ImageCompressError');
                        }
                    })
                    .then( function(compressResult) {
                        resolve(compressResult);
                    });
            });
    });
}

function readFile(file, onProgress, onComplete) {
    var filename = file.name;
    return new Promise(function(resolve, reject) {
        var fileReader = new FileReader();


        fileReader.onload = function() {
            resolve(fileReader.result);
        };
        fileReader.onabort = function(e) { reject('FileLoadAbort', filename); };

        fileReader.onerror = function(e) { reject('FileLoadError', filename); };

        if (onProgress) {
            fileReader.onprogress = onProgress;
        }

        if (onComplete) {
            fileReader.onloadend = onComplete;
        }

        fileReader.readAsDataURL(file);
    });
}

function loadImage(imgDataUrl) {
    return createImageBitmap(imgDataUrl)
}

function simpleCompress(img, filename, q, maxW, maxH) {
    return new Promise(function(resolve, reject){

        var sf = getScaleFactor(img.width, img.height, maxW, maxH);

        var w = sf < 1 ? img.width * sf : img.width;
        var h = sf < 1 ? img.height * sf : img.height;
        var quality = sf < 1 ? 0.9 : 0.7;
        var type = 'image/jpeg';

        var myCanvas = new OffscreenCanvas(w, h);
        myCanvas.width = w;
        myCanvas.height = h;

        var ctx = myCanvas.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);

        myCanvas.convertToBlob({ type, quality }).then(function(blob) {
            var file = new File([blob], filename, { type });
            resolve(file);
        }).catch(function() {
            reject('FileConvertError');
        });
    });
}

function compressImageToFile(img, filename, options = {}) {

    return new Promise(function(resolve, reject) {
        console.log('compressImageToFile #214');
        var success = {
            changed: false,
            file: null,
            width: 0,
            height: 0,
            factor: 1,
            quality: 1,
            compressQuality: 1,
            compressRound: 0,
            origin: {
                width: 0,
                height: 0,
                size: 0
            }
        };
        success.compressRound = 0;

        var opts = Object.assign([], defaultConf, options);

        success.compressStart = opts.quality;
        success.origin.width = img.width;
        success.origin.height = img.height;
        if (!opts.currSize) {
            opts.currSize = img.width * img.height;
        } else {
            success.origin.size = opts.currSize;
        }

        var compressQuality = opts.quality;

        var sf = getScaleFactor(img.width, img.height, opts.maxWidth, opts.maxHeight);

        if ( (!opts.maxSize || (opts.currSize > 0 && opts.maxSize > opts.currSize)) && sf >= 1) {
            console.log('[REJECT] Filesize must not be changed [' + MSG_FileSizeMustNotBeChanged + ']');
            reject(MSG_FileSizeMustNotBeChanged);
        }

        var w = sf < 1 ? img.width * sf : img.width;
        var h = sf < 1 ? img.height * sf : img.height;

        success.width = w;
        success.height = h;

        // var myCanvas = document.createElement('canvas');
        var myCanvas = new OffscreenCanvas(w, h);
        myCanvas.width = w;
        myCanvas.height = h;

        var ctx = myCanvas.getContext('2d');
        ctx.drawImage(img, 0, 0, w, h);

        var _helperConvertBlobToFile = function(blob) {
            success.compressRound += 1;

            if (!maxSize || blob.size < maxSize || minQuality >= compressQuality) {
                success.changed = true;
                success.file = new File([blob], fileName, { type: "image/jpeg" });
                success.quality = compressQuality;
                return resolve( success );
            }

            delete compressedFile;

            if (compressedSize > maxSize * 1.1) {
                compressQuality = Math.max(minQuality, compressQuality - .1);
            } else {
                compressQuality = Math.max(minQuality, compressQuality - .05);
            }

            console.log('compress #309 resolve', { success });
            convertCanvasToFile();
        };

        var convertCanvasToFile = function() {

            if ('convertToBlob' in myCanvas) {
                myCanvas.convertToBlob({ type: 'image/jpeg', quality: compressQuality}).then(function(blob){
                    _helperConvertBlobToFile(blob)
                });
            } else if ('toBlob' in myCanvas){
                myCanvas.toBlob(_helperConvertBlobToFile, 'image/jpeg', compressQuality);
            } else {
                reject('Cannot Create Canvas for compressing!');
            }
        };
        convertCanvasToFile();
    });
}

