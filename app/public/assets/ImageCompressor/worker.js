
importScripts('compressor.short.js');


onmessage = function (e) {
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
        command = 'compressImageFile';
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
                        simpleCompress(img, file.name, maxWidth, maxHeight)
                            .then( function(rsltFile) {
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
