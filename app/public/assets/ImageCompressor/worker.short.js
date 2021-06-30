
importScripts('compressor.js');

function getProgressLogger(filename) {
    return function(e) {
        if (e.lengthComputable) {
            console.log('[WORKER-THREAD] Progress for ' + filename, (e.loaded * 100 / e.total).toFixed(2) + '%');
        }
    };
}

function getCompleteLogger(filename) {
    return function(e) {
        if (e.lengthComputable) {
            console.log('[WORKER-THREAD] Completed ' + filename, (e.loaded * 100 / e.total).toFixed(2) + '%');
        }
    };
}

onProgress = function(e) {
    console.log('[WORKER-THREAD] Message From Main-Script: ', e.data);
};

onmessage = function (e) {
    var data = e.data;
    var dataType = typeof e.data;
    var dataIsObject = dataType === 'object';
    var command = dataIsObject && 'command' in e.data ? e.data.command : null;
    var commandId = dataIsObject && 'commandId' in e.data ? e.data.commandId : '';

    var responseObject = {
        resultType: '',
        refCommand: command,
        refCommandId: commandId,
        carry: e.data.carry ?? null,
        error: null,
        data: null,
    };

    console.log('[WORKER-THREAD] Message From Main-Script: ', dataType);
    var workerResult = 'Response typeof data: ' + dataType;
    console.log('[WORKER-THREAD] Posting message back to main script', { workerResult });

    if (dataIsObject && e.data instanceof File) {
        command = 'readFile';
    }

    if (command) {
        // postMessage('Received Message with command ' + command);

        switch(command) {
            case 'compressImageFile':
                var file = e.data.data;
                var maxWidth = 600;
                var maxHeight = 600;
                loadImage(file)
                    .then(function(img) {
                        simpleCompress(img, file.name, maxWidth, maxHeight).then( function(rsltFile) {
                            responseObject.resultType = 'compressedImageFile';
                            responseObject.data = rsltFile;
                           postMessage(responseObject);
                        })
                            .catch(function() {
                            responseObject.resultType = 'error';
                            responseObject.error = 'ImageCompressError';
                            postMessage(responseObject);
                        });
                    }).catch(function() {
                        responseObject.resultType = 'error';
                        responseObject.error = 'ImageLoadError';
                        postMessage(responseObject);
                    });
                break;

            case 'readFile':
                var file = (e.data instanceof File) ? e.data : e.data.data;
                var onProgress = getProgressLogger(file.name);
                var onComplete = getCompleteLogger(file.name);
                // postMessage('Start Processing: ' + command);
                readFile(file, onProgress, onComplete).then(function(data) {
                    // postMessage('File fully readed');
                    responseObject.resultType = 'fileread';
                    responseObject.data = data;
                    postMessage(responseObject);
                });
                break;

            case 'resizeImageFile':
                // All in One: Get File, load as Image, write to Canvas and convert to resized File
                console.log('[Worker-Thread] called resizeImageFile #58');
                var conf = Object.assign({}, e.data.conf ?? {}, defaultConf);
                if (!conf.fileName && e.data.data instanceof File) {
                    conf.fileName = e.data.data.name;
                    conf.currSize = e.data.data.size;
                }

                // postMessage('Start Processing: ' + command, { conf });
                loadImage(e.data.data)
                    .then(function(rsltImg) {
                        console.log('[Worker-Thread] loaded Image: ' + conf.fileName);
                        compressImageToFile(rsltImg, conf).then(function(resultWithFileData) {
                            // postMessage('Start PResizing: ' + command, conf.fileName);
                            console.log('[Worker-Thread] resized Image: ' + conf.fileName);
                            responseObject.resultType = 'imageresized';
                            responseObject.data = resultWithFileData;
                            postMessage(responseObject);
                            // self.close();
                        }).catch(function(){
                            console.error('[Worker-Thread] ERROR cannot resize Image: ' + conf.fileName);
                            responseObject.resultType = 'error';
                            responseObject.error = [].slice.apply(arguments);
                            postMessage(responseObject);
                            // self.close();
                        });
                    })
                    .catch(function(msg = '') {
                        console.error('[Worker-Thread] ERROR cannot load Image: ' + conf.fileName);
                        responseObject.resultType = 'error';
                        if (msg === MSG_FileSizeMustNotBeChanged) {
                            responseObject.error = msg;
                        } else {
                            responseObject.error = [].slice.apply(arguments);
                        }
                        postMessage(responseObject);
                        // self.close();
                    });
                break;

            case 'resizeByImageFileData':
                console.log('[Worker-Thread] called resizeByImageFileData #68');
                var conf = Object.assign({}, e.data.conf ?? {}, defaultConf);
                conf.fileName = e.data.filename || '';
                console.log({
                    defaultConf,
                    conf
                });

                // postMessage('Start Processing: ' + command, { conf });
                compressImageToFile(e.data.data, conf).then(function(result) {
                    console.log('[Worker-Thread] called resizeByImageFileData #76');
                    postMessage({
                        resultType: 'imageresized',
                        refCommand: command,
                        refCommandId: commandId,
                        data: result
                    });
                }).catch(function(){
                    console.log('[Worker-Thread] called resizeByImageFileData #84');
                    postMessage({
                        resultType: 'error',
                        refCommand: command,
                        refCommandId: commandId,
                        error: [].slice.apply(arguments),
                        data: null,
                    });
                });
                break;

            case 'loadImageFileData':
                var filename = 'filename' in e.data ? e.data.filename : '';

                // postMessage('Start Processing: ' + command, { conf });
                loadImage(e.data.data).then(function(image){
                    postMessage('Image loaded');
                    postMessage({
                        resultType: 'imageloaded',
                        refCommand: command,
                        refCommandId: commandId,
                        filename,
                        data: image
                    });
                }).catch(function() {
                    throw new Error('Bild konnte nciht geladen werden ' + filename);
                });
                break;

            default:
                postMessage('Received Message with unknown command ' + command);
                self.close();
        }
    }
    else {
        postMessage('Unbekannte Message: ', { message: e.data });
        self.close();
    }
};
