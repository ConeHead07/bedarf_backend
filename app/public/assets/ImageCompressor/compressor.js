
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

function fileAsImgSrc(file, img) {
    var fd = new FileReader();
    fd.onload = function(e) {
        img.src = e.target.result;
    };

    fd.readAsDataURL(file);
}

function add(imgFile) {
    var fileReadItem = {
        file: imgFile,
        total: imgFile.size,
        loaded: 0,
        status: 'pending'
    };
    readingFiles.push(fileReadItem);
}

function run() {
    for(var item of fileReadItem) {
        var _cnf = $.extend({}, defaultConf, {
            currSize: item.file.size,
            fileName: item.file.name
        });

        compress(item.file, _cnf);
    }
}

function compress(imgFile, opts) {
    return new Promise(function(resolve, reject) {
        var settings = $.extend({}, opts, {
           maxSize: 0,
           maxWidth: 0,
           maxHeight: 0,
           minQuality: .6,
           currSize: imgFile.size,
           fileName: imgFile.name
        });

        readFile(imgFile)
            .catch(function() {
                reject('FileReadError');
            })
            .then( function(fileData) {
                loadImage(fileData)
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
    });
}

function readFile(file, onProgress, onComplete) {
        return new Promise(function(resolve, reject) {
        var fileReader = new FileReader();
        var readingItem = {
            fileReader,
            file,
            start: Date.now(),
            total: file.size,
            loaded: 0
        };

        fileReader.onloadstart = function(e) {
          readingFiles.push( readingItem );
        };

        fileReader.onabort = function(e) {
            console.log('imgInputScale #100 read abort of file ' + fileName);
            reject('FileLoadAbort');
        };

        console.log('imgInputScale #102');
        fileReader.onerror = function(e) {
            console.log('imgInputScale #89 read error of file ' + fileName, arguments);
            reject('FileLoadError');
        };

        console.log('imgInputScale #107');
        fileReader.onprogress = function(e) {
            if (!e.lengthComputable) {
                return;
            }

            readingItem.total = e.total;
            readingItem.loaded = e.loaded;
            if (onProgress && typeof onProgress === 'function') {
                onProgress(e);
            }
        };

        fileReader.onload = function() {
            resolve(fileReader.result);
        };

        fileReader.onloadend = function(e) {
            if (onComplete && typeof onComplete === 'function') {
                onComplete(e);
            }
            cleanReadingFileList(readingItem);
        };
        fileReader.readAsDataURL(file);
    });
}

function cleanReadingFileList(readingItem) {
    for(var i in readingFiles) {
        if (readingFiles[i] === readingItem) {
            readingFiles[i] = null;
            delete( readingFiles );
            break;
        }
    }
}

function cleanLoadingImageList(loadingItem) {
    for (var i in loadingImages) {
        if (loadingImages[i] === loadingItem) {
            loadingImages[i] = null;
            delete loadingImages[i];
        }
    }
}

function loadImage(imgDataUrl) {
    if (typeof Image === 'undefined') {
        return createImageBitmap(imgDataUrl)
    }
    return new Promise(function(resolve, reject) {
        var image = new Image();
        var loadingItem = {
            image,
            data: imgDataUrl,
            start: Date.now(),
            total: imgDataUrl.length,
            loaded: 0
        };
        image.onabort = function(e) {
            console.log('imgInputScale #97 img load abort ' + fileName, arguments);
            reject('ImageLoadAbort');
        };
        image.onerror = function(e) {
            console.log('imgInputScale #97 img load error ' + fileName, arguments);
            reject('ImageLoadError');
        };
        image.onloadend = function(e) {
            cleanLoadingImageList(loadingItem);
        };
        image.onloadstart = function(e) {
            loadingImages.push( loadingItem );
        };

        image.onload = function(e2) {
            resolve(image);
        };
    });
}

var imgCounter = 0;
function getNextImgNr() {
    imgCounter++;
    return imgCounter;
}

function compressImageToFile(img, filename, options = {}) {
    if(arguments.length === 2) {
        if (['string', 'number'].indexOf(typeof filename) === -1) {
            options = filename;
            filename = '';
        }
    }
    console.log('compressImageToFile #212', arguments);
    return new Promise(function(resolve, reject) {
        console.log('compressImageToFile #214');
        var success = {
            changed: false,
            file: null,
            width: 0,
            height: 0,
            size: 0,
            origin: {
                width: 0,
                height: 0,
                size: 0
            }
        };
        var maxSize = options.maxSize ?? 0;
        var currSize = options.currSize ?? 0;
        var maxWidth = options.maxWidth ?? 0;
        var maxHeight = options.maxHeight ?? 0;
        var quality = options.quality ?? .9;
        var minQuality = options.minQuality ?? .5;
        var fileName = '';
        if (filename && (typeof filename === 'string') && filename.length > 0) {
            fileName = filename;
        } else if (filename && (typeof filename === 'number')) {
            fileName = filename.toString(10);
        } else if ('fileName' in options && options.fileName !== '') {
            fileName = (typeof options.fileName === 'number')
                        ? (+options.fileName).toString(10)
                        : options.fileName;
        } else {
            fileName = 'Image' + getNextImgNr()() + '.jpg';
        }

        success.origin.width = img.width;
        success.origin.height = img.height;
        success.origin.size = currSize;

        var sf = getScaleFactor(img.width, img.height, maxWidth, maxHeight);
        console.log('Available Dimensions: ', {
            img,
            imgWidth: img.width,
            imgHeight: img.height,
            sf,
            maxWidth,
            maxHeight,
            currSize,
            maxSize
        });
        if ( (!maxSize || maxSize > currSize) && sf >= 1) {
            console.log('Cancel Resizing: ', {
                img,
                imgWidth: img.width,
                imgHeight: img.height,
                sf,
                maxWidth,
                maxHeight,
                currSize,
                maxSize
            });
            console.log('[REJECT] Filesize must not be changed [' + MSG_FileSizeMustNotBeChanged + ']');
            reject(MSG_FileSizeMustNotBeChanged);
        }
        var w = sf < 1 ? img.width * sf : img.width;
        var h = sf < 1 ? img.height * sf : img.height;
        success.width = w;
        success.height = h;

        console.log('compressImageToFile #257 Before OffscreenCanvas(', w, h, ')');
        var myCanvas = new OffscreenCanvas(w, h);
        console.log('compressImageToFile #259 After OffscreenCanvas(', w, h, ')');
        // var myCanvas = document.createElement('canvas');
        myCanvas.width = w;
        myCanvas.height = h;

        console.log('compressImageToFile #264 After OffscreenCanvas(', w, h, ')');
        var ctx = myCanvas.getContext('2d');

        var compressQuality = quality;
        success.compressStart = quality;
        success.compressRound = 0;

        console.log('compressImageToFile #269 After OffscreenCanvas(', w, h, ')');
        ctx.drawImage(img, 0, 0, w, h);

        var _helperConvertBlobToFile = function(blob) {
            success.compressRound += 1;
            console.log('compress #276 scaled ' + fileName  + ', compressQuality: ' + compressQuality, {
                srcW: img.width,
                srcH: img.height,
                maxWidth,
                maxHeight,
                scaleFactor: sf,
                trgW: w,
                trgH: h,
                oldSize: currSize,
                newSize: blob.size,
            });
            var compressedFile = new File([blob], fileName, { type: "image/jpeg" });
            var compressedSize = compressedFile.size;

            console.log('compress #290');
            if (!maxSize || compressedSize < maxSize || minQuality >= compressQuality) {
                success.changed = true;
                success.file = compressedFile;
                success.size = compressedSize;
                success.quality = compressQuality;
                console.log('compress #296 resolve', { success });
                return resolve( success );
            }

            console.log('compress #300 resolve');
            delete compressedFile;

            if (compressedSize > maxSize * 1.1) {
                compressQuality = Math.max(minQuality, compressQuality - .1);
            } else {
                compressQuality = Math.max(minQuality, compressQuality - .05);
            }

            console.log('compress #309 resolve', { success });
            convertCanvasToFile();
            console.log('compress #311 resolve', { success });
        };

        var convertCanvasToFile = function() {
            console.log('compressImageToFile #273 After OffscreenCanvas(', w, h, ')');

            if ('convertToBlob' in myCanvas)
            myCanvas.convertToBlob({ type: 'image/jpeg', quality: compressQuality}).then(function(blob){
                _helperConvertBlobToFile(blob)
            });

            if ('toBlob' in myCanvas)
            myCanvas.toBlob(_helperConvertBlobToFile, 'image/jpeg', compressQuality);
        };
        convertCanvasToFile();
    });
}

function imgInputScale(fileInputItem) {
    console.log('#74 imgInputScale(' +
        fileInputItem.name + ' ' +
        fileSizeReadable(fileInputItem.size)
    );
    return new Promise(function(resolve, reject){
        console.log('imgInputScale #76');
        var success = {
            changed: false,
            file: null,
            width: 0,
            height: 0,
            size: 0,
            origin: {
                width: 0,
                height: 0,
                size: fileInputItem.size
            }
        };
        console.log('imgInputScale #81');

        var file = fileInputItem;
        var KB = 1024;
        var maxWidth = 1024;
        var maxHeight = 1024;
        var maxSize = 100 * KB;
        var fileName = file.name;
        console.log('imgInputScale #89');

        if (file.size <= maxSize) {
            console.log('imgInputScale #92 size of file ' + fileName + ' is OK (' + fileSizeReadable(file.size));
            resolve(success);
        }
        console.log('imgInputScale #95');

        var fileReader = new FileReader();
        fileReader.onabort = function(e) {
            console.log('imgInputScale #100 read abort of file ' + fileName);
            reject('FileLoadAbort');
        };
        console.log('imgInputScale #102');
        fileReader.onerror = function(e) {
            console.log('imgInputScale #89 read error of file ' + fileName, arguments);
            reject('FileLoadError');
        };
        console.log('imgInputScale #107');
        fileReader.onprogress = function(e) {};
        console.log('imgInputScale #109');
        fileReader.abort();
        fileReader.onloadend = function(e) {
            var i = readingFiles.indexOf(file);
            if (i > -1) {
                readingFiles[i] = null;
                readingFiles = readingFiles.splice(i, 1);
            }
        }

        fileReader.onload = function(e) {
            console.log('imgInputScale #94 load file ' + fileName, arguments);

            var myImg = new Image();
            myImg.onabort = function(e) {
                console.log('imgInputScale #97 img load abort ' + fileName, arguments);
                reject('ImageLoadAbort');
            };
            myImg.onerror = function(e) {
                console.log('imgInputScale #97 img load error ' + fileName, arguments);
                reject('ImageLoadError');
            };
            myImg.onloadend = function(e) {
                loadingImages = loadingImages.slice();
                for (var i = 0; i < loadingImages.length; i++) {
                    if (loadingImages[i].image === myImg) {
                        loadingImages[i] = null;
                        loadingImages = loadingImages.splice(i, 1);
                    }
                }
            };
            myImg.onload = function(e2) {
                console.log('imgInputScale #105 img load success ' + fileName, arguments);
                success.origin.width = myImg.width;
                success.origin.height = myImg.height;

                var sf = getScaleFactor(myImg.width, myImg.height, maxWidth, maxHeight);
                var w = sf < 1 ? myImg.width * sf : myImg.width;
                var h = sf < 1 ? myImg.height * sf : myImg.height;
                success.width = w;
                success.height = h;

                var myCanvas = document.createElement('canvas');
                myCanvas.width = w;
                myCanvas.height = h;
                var ctx = myCanvas.getContext('2d');

                ctx.drawImage(myImg, 0, 0, w, h);
                myCanvas.toBlob(function(blob) {
                    console.log('imgInputScale #118 scaled ' + fileName, {
                        srcW: myImg.width,
                        srcH: myImg.height,
                        maxWidth,
                        maxHeight,
                        scaleFactor: sf,
                        trgW: w,
                        trgH: h,
                        oldSize: file.size,
                        newSize: blob.size,
                    });
                    success.changed = true;
                    success.file = new File([blob], fileName, { type: "image/jpeg" });
                    success.size = success.file.size;
                    resolve(success);
                }, 'image/jpeg', .7);
            };
            myImg.src = fileReader.result;
            loadingImages.push({
                file: file,
                filename: file.name,
                image: myImg
            });
        };
        fileReader.readAsDataURL(file);
        readingFiles.push(file);
    });
}
