
function readTextFile(file) {
    console.log('readTextFile #3 start reading objektbuch tabelle ', { file });
    return new Promise( function(resolve, reject) {
        var fr = new FileReader();
        fr.onload = function(e) {
            resolve(e.target.result);
        };
        fr.onerror = function(e) {
            reject(e);
        };
        fr.onabort = function(e) {
            reject(e);
        };
        fr.onprogress = function(e) {
            if (e.lengthComputable) {
                console.log('Loaded ' + e.loaded + ' of ' + e.total +
                    ' => ' + (e.loaded * 100 / e.total).toFixed(2) + '%');
            } else {
                console.log('onprogress #20', { e });
            }
        };
        fr.readAsText(file);
    });
}

function readImageFilenames(file) {
    console.log('readImageFilenames #25 start reading objektbuch tabelle ', { file });
    return new Promise( function(resolve, reject) {
        console.log('readImageFilenames #28', { file });
        readTextFile(file).then(function (txt) {
            console.log('readImageFilenames #30', {txt});
            var matches = txt.match(/src=".*?\.jpg"/g);
            var filenames = [];
            for(var i = 0; i < matches.length; i++) {
                var f = matches[i].substr(5);
                f = f.substr(0, f.length - 1 );
                filenames.push( f );
            }
            console.log({matches, filenames });
            resolve(filenames);
        }, function(reason) {
            console.log('readImageFilenames #41 was rejected ', { file });
            reject(reason);
        });
    });
}
