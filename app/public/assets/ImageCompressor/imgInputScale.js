var readingFiles = [];
var loadingImages = [];

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
        img.onload = function(e) {

        };
        img.src = e.target.result;
    };

    fd.readAsDataURL(file);
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

self.addEventListener('message', function(e) {
    if (e.data && e.data.command && e.data.file) {
        switch(e.data.command) {
            case 'scaleImage':
                imgInputScale(data.file)
                    .then( function() {

                    })
        }
    }
    self.postMessage(e.data);
}, false);
