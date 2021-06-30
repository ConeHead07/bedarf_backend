@extends('layouts.master')
@section('title', $title ?? 'Administration')
@section('sidebar')
@endsection
@section('content')
<style>
    .ui.steps .step.active {
        cursor: auto;
        background: #e3f0fd;
    }
    .ui.steps .step.active:after {
        background: #e3f0fd;
    }

    label.upload input[type=file],
    label.upload + input[type=file] {
        display:none;
    }

    label .hint {
        font-size:0.8em;
        display:block;
    }

    .ui.popup.auto-big {
        min-width: 50vw;
        max-width: 100vw;
        overflow: auto;
    }

    .autocol-2 {
        column-count: 4;
        column-gap:1rem;
        column-rule-style: solid;
        column-rule-width: 1px;
        column-rule-color: lightblue;
        column-width: 250px;
        width:100%;
        --height:100vh;
        max-width:1220px;
        --max-height:600px;
        overflow:auto;
    }
    .autocol-2 ol {
        margin-top:0;
        margin-bottom:0;
    }

    .display-debug,
    .ui.display-debug,
    .ui.button.display-debug {
        display: none;
    }
    .ui.grid.segment.segment-with-hint,
    .segment.segment-with-hint {
        margin-bottom: .1rem;
    }

    .segment.segment-with-hint + .segment-hint {
        margin-top: .1rem;
        margin-bottom: 1rem;
    }
    .segment-hint {
        color: #767676;
        font-size: .8em;
    }
</style>
<script>
    var aMandanten = <?= json_encode( $Mandanten ?? []) ?>;
    var aStandorte = <?= json_encode( $Standorte ?? []) ?>;
</script>
<script xsrc="/assets/jslibrary/myInventarUploader.js"></script>
<script src="/assets/jslibrary/objektbuchReader.js"></script>

<div id="importSteps" class="ui ordered mini steps">

    <div class="segement">
        <div class="disabled step step-1 step-upload-inventar"
             data-stepUrl="/api/admin/import/upload"
             title="Upload/Dateiübertragung">
            <div class="content">
                <div class="title">Upload</div>
                <div class="description">Inventardaten</div>
            </div>
        </div>
        <div class="ui bottom attached progress">
            <div class="bar"></div>
        </div>
    </div>
    <div class="disabled step step-2 step-copy-inventar"
         title="Verarbeitungsphase nach Upload bis Server-Response">
        <div class="content">
            <div class="title">Kopieren</div>
            <div class="description">Inventardaten</div>
        </div>
    </div>

    <div class="disabled step step-3 step-import-inventar"
         data-stepUrl="/api/admin/import/byuploadid/${jobid}">
        <div class="content">
            <div class="title">Import</div>
            <div class="description">Inventardaten</div>
        </div>
        <div class="ui bottom attached progress">
            <div class="bar"></div>
        </div>
    </div>

    <div class="segment">
        <div class="disabled step step-4 step-upload-images"
            data-stepUrl="/api/admin/import/uploadImages/${jobid}">
            <div class="content">
                <div class="title">Upload</div>
                <div class="description">Bilder</div>
            </div>
        </div>
        <div class="ui bottom attached progress">
            <div class="bar"></div>
        </div>
    </div>

    <div class="disabled step step-5 step-import-images"
         data-stepUrl="/api/admin/imageimport/${jobid}/importKatalogImages">
        <div class="content">
            <div class="title">Import</div>
            <div class="description">Bilder</div>
        </div>
    </div>
</div>

<div class="statusMsg"></div>

<input type="text" style="display:none" id="activeStep" value="0">
<input type="text" style="display:none" id="imagesDone" value="init">
<input type="text" style="display:none" id="imagesSelectionChanged" value="init">
<input type="text" style="display:none" id="imagesCheckedChanged" value="init">

<form id="fupForm" class="ui form" method="post" style="margin-top:2rem" action="/api/admin/import/upload" enctype="multipart/form-data">
    <div class="field">
        <label>Mandant</label>
        <select name="mid" id="inputMandant" class="ui search dropdown">
            <option value="">Mandant auswählen</option>
        </select>
    </div>

    <div class="field ui transition hidden">
        <label>Standort</label>
        <select name="gid" id="inputStandort" class="ui search dropdown">
            <option value="">Standort auswählen</option>
        </select>
    </div>


    <div class="ui segment equal width grid input-box-inventar segment-with-hint">
        <label for="inputFile" class="ui column middle green button inverted upload">
            <i class="ui upload icon"></i>
            Upload Zipped CSV-Files
        </label>
        <input type="file" id="inputFile" name="InventarDaten" placeholder="Daten.zip" accept=".zip">
        <div class="column"><span class="file-name"></span></div>
        <div class="column"><span class="file-size"></span></div>
    </div>
    <div class="segment-hint" style="padding: 0.1rem .1rem;color: silver;font-size: .8em;">
        Zip-File sollte "Import Inventar.csv" und "Import Raeume.csv" enthalten.
    </div>

    <div class="ui segment equal width grid input-box-objektbuch segment-with-hint">
        <label for="inputOBT" class="ui column middle green button inverted upload">
            <i class="ui upload icon"></i>
            Upload Objektbuch-Tabelle (.html)
        </label>
        <input type="file" id="inputOBT" name="InventarOBT" placeholder="ObjektbuchTabelle.html" accept=".htm,.html">
        <div class="column"><span class="file-name"></span></div>
        <div class="column"><span class="file-size"></span></div>
    </div>
    <div class="segment-hint" style="padding: 0.1rem .1rem;color: silver;font-size: .8em;">
        Wird nur für alte Bildzuordnung benötigt!
    </div>


    <div class="ui segment equal width grid input-box-images segment-with-hint">
        <label for="inputImg" class="ui column middle green button inverted upload">
            <i class="ui upload icon"></i>
            Upload Objektbuch-Bilder (.jpg)
        </label>
        <input type="file" id="inputImg" multiple="" name="InventarImg[]" placeholder="Bilder" accept="image/jpeg">
        <div class="column"><span class="file-name"></span></div>
        <div class="column"><span class="file-size"></span></div>
    </div>
    <div class="segment-hint" style="padding: 0.1rem .1rem;color: silver;font-size: .8em;">
        Bennant nach Objektbuch-ID-Spalte + .jpg
    </div>

    <div id="progressFiles"></div>

    <div class="field hidden" style="display: none;">
        <div class="ui checkbox">
            <input id="testcheck01" type="checkbox" tabindex="0" class="hidden">
            <label for="testcheck01">I agree to the Terms and Conditions</label>
        </div>
    </div>
    <button class="ui button submitBtn" type="submit" disabled>Submit</button>

</form>

<div id="bntSubmitImages" class="ui button primary display-debug">Submit Images</div>
<div>
    <table id="fileTable" class="ui celled table" style="display: none">
        <thead>
        <tr>
            <th>Bild</th>
            <th>Datei</th>
            <th>Größe <span id="fileSizeSum"></span></th>
            <th>OBT</th>
            <th>Aktion</th>
        </tr></thead>
        <tbody id="fileList" style="height:50vh;overflow-y: auto">
        </tbody>
    </table>
</div>

<script>

    var frmUploadValidation = {
            fields: {
                mid       : 'empty',
                inputFile : 'empty'
            }
        }
    ;

    var imgPlaceholder = new Image();
    imgPlaceholder.src = '/assets/waitMe/image_placeholder.png';

    var readingFiles = [];
    var loadingImages = [];
    var allFiles = [];
    var objektbuchImages = [];

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
            var maxWidth = 800;
            var maxHeight = 800;
            var maxSize = 50 * KB;
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

    function showResponseError(response) {
        var errHtml = '';
        if (typeof response === 'object' && ('responseJSON' in response) ) {
            var rspJson = response.responseJSON;
            if (rspJson.message) {
                errHtml += '<div>' + rspJson.message + '</div>' + "\n";
            }
            if (rspJson.exception) {
                errHtml += '<div>Exception: ' + rspJson.exception + '</div>' + "\n";
            }
            if (rspJson.file) {
                errHtml += '<div>' + rspJson.file + '</div>' + "\n";
            }
            if (rspJson.line) {
                errHtml += '<div>In Zeile ' + rspJson.line + '</div>' + "\n";
            }
            if (rspJson.trace && Array.isArray(rspJson.trace) && rspJson.trace.length > 0) {
                for (var i = 0; i < rspJson.trace.length; i++) {
                    errHtml += '<ul>';
                    var stackItem = rspJson.trace[i];
                    for (var k in stackItem ) {
                        if (!stackItem.hasOwnProperty(k)) {
                            continue;
                        }
                        errHtml += '<li>' + k + ' : ' + stackItem[k] + '</li>';
                    }
                    errHtml += '</ul>';
                }
            }
        } else {
            errHTML = JSON.stringify(response);
        }
        $('.statusMsg').prepend(
            $('<p data-line="499" class="ui negative message"/>').html(errHtml)
        );
    }

    var setStepStatus = function() {};
    var originSelectedFiles = [];
    var uploadSelectedFiles = [];

    $(function () {
        var jobid = 0;
        var step = 0;
        var stepBox = null;
        var stepItem = null;
        var stepProgress = null;
        var stepList = [
            'Form Input',
            'Upload Inventar / Objekttabelle',
            'Speichern Inventar / Objektabelle',
            'Import Inventar',
            'Upload Bilder',
            'Import Bilder'
        ];
        var lastStep = null;
        var btnSubmit = null;

        function getFormSubmitButton() {
            if (!btnSubmit || btnSubmit === null || btnSubmit.length === 0) {
                btnSubmit = $("button.submitBtn[type=submit]");
            }
            return btnSubmit;
        }

        var frmUpload = $('form#fupForm').form(frmUploadValidation);
        frmUpload
            .find('#inputMandant, #inputFile, inputImg')
            .on('change', function (e) {});

        function initStepStatus() {
            stepBox = $('#importSteps');
            stepBox.find('.step').removeClass('active').removeClass('completed').addClass('disabled');
            stepBox.find('+ .progress').attr('data-percent', 0).data('percent', 0);
        }

        function onProgressImgScalingCheck(numChecked, numTotal) {
            console.log('ImgScaleAndResizeCheck in Progress: ', { numChecked, numTotal });

            if (numChecked >= numTotal) {
                onFinishImgScalingCheck(numChecked, numTotal);
            }
        }

        function onFinishImgScalingCheck(numChecked, numTotal) {
            console.log('ImgScaleAndResizeCheck Finished: ', { numChecked, numTotal });
            var btn = getFormSubmitButton();
            if (btn && btn.length) {
                btn.prop('disabled', false).waitMe('hide');
            }
        }

        setStepStatus = function(stepNr, status, progress) {
            var step = getCurrStep();
            if (progress > 0) {
                progress = parseFloat(parseFloat(progress).toFixed(1));
            }
            if (stepNr === 5 && (status === 'finished' || status === 'error' || progress === 100.0)) {
                $('#fupForm').waitMe('hide');
            } else if (stepNr >= stepList.length) {
                $('#fupForm').waitMe('hide');
            } else {
                $('#fupForm').waitMe({
                    effect: 'ios',
                    text: "Bitte warten, Daten werden verarbeitet!<br>\n" +
                        stepNr + '. ' + stepList[stepNr] + (progress > 0 ? progress.toString().replace('.', ',') + '%' : '')
                });
            }

            if (null === stepBox) {
                initStepStatus();
            }

            if (stepNr != step || !stepItem) {
                if (stepItem) {
                    stepItem.removeClass('active').addClass('completed');
                    stepProgress.progress({percent: 100});
                }
                stepItem = stepBox.find('.step.step-' + stepNr);
                stepProgress = stepItem.find('+ .progress');
                step = stepNr;
            }

            console.log('stepStatus', stepNr, status, progress);
            stepItem.removeClass('disabled').addClass('active');
            if (['complete', 'finished'].indexOf(status) > -1 || progress === 100) {
                stepItem.addClass('completed');
                stepProgress.progress({percent: progress });
            }
        };

        var setNextStep = function(nextStep) {
            lastStep = step;
            step = nextStep;
            $("#activeStep").val(step);
            return step;
        };

        var getNextStep = function() {
            lastStep = step;
            ++step;
            $("#activeStep").val(step);
            return step;
        };

        var getCurrStep = function() {
            return step;
        };

        var aMandanten = <?= json_encode($Mandanten ?? []) ?>;
        var aStandorte = <?= json_encode($Standorte ?? []) ?>;

        var activeMandant = '';
        var activeStandort = '';
        var checkedFilesIds = [];
        var numUploadedFiles = 0;
        var numFinishedFiles = 0;
        var numLoadedFiles = 0;
        var numAbortedFiles = 0;
        var numErrorFiles = 0;
        var numCheckedFiles = 0;
        var numFixedFiles = 0;


        function fileSizeUnit(fsize) {
            if (fsize < 1024) return 'B';
            if (fsize < 1024 * 1024) return 'KB';
            return 'MB'
        }

        function uploadSingleFile(url, inputName, file, i, statusCallback) {
            var fileId = i;
            var ajax = new XMLHttpRequest();

            $("#filestatus_" + i).html('').append(
                $('<div class="ui indicating progress">' +
                    '<div class="bar"><div class="progress"></div></div>' +
                    '<div class="label">Fileupload</div>' +
                    '</div>')
            );
            var fileName = file.name;
            var fileSize = file.size;
            var fileLabel = 'progress ' + fileName + ' (' + fileSize + ') => ';
            var progUnit = fileSizeUnit(file.size);
            var progTotal = fileSizeReadable(file.size, progUnit, true);
            var progBar = $('#filestatus_' + i).find('.ui.progress').progress({
                total: file.size,
                value: 0,
                label: 'percent',
                text: {
                    success: 'Uploaded ' + progTotal,
                    error: 'Upload-Error',
                }
            }).progress('set label', 'waiting for upload ....');

            //Progress Listener
            ajax.upload.addEventListener("progress", function (e) {
                var lengthComputable = e.lengthComputable;
                console.log(fileLabel + e.loaded, { lengthComputable} );
                progBar.progress('set progress', e.loaded);
                progBar.progress('set label', "Uploaded " + fileSizeReadable(e.loaded, progUnit, false) + " of " + progTotal);
            }, false);

            //Load Listener
            ajax.addEventListener("load", function (e) {
                progBar.progress('set success').progress('set label', 'Upload finsihed');
                // $("#status_" + fileId).text('Upload Finished!');
                // $('#progressbar_' + fileId).css("width", "100%");
                // progressCallback(i, file.size );

                //Hide cancel button
                var _cancel = $('#cancel_' + fileId);
                _cancel.hide();
                statusCallback(i, 'load');
            }, false);

            //Error Listener
            ajax.addEventListener("error", function (e) {
                progBar.progress('set error').progress('set label', 'Upload failed');
                // $("#status_" + fileId).text("Upload Failed");
                statusCallback(i, 'error');
            }, false);

            //Abort Listener
            ajax.addEventListener("abort", function (e) {
                $("#status_" + fileId).text("Upload Aborted");
                statusCallback(i, 'abort');
            }, false);

            ajax.open("POST", url); // Your API .net, php

            console.log('uploading file ' + file.name + " " + file.size);
            var uploaderForm = new FormData(); // Create new FormData
            uploaderForm.append(inputName, file, file.name); // append the next file for upload
            ajax.send(uploaderForm);

            //Cancel button
            var _cancel = $('#cancel_' + fileId);
            _cancel.show();

            _cancel.on('click', function () {
                ajax.abort();
            });

            return ajax
        }


        var oM = $("#inputMandant");
        var oS = $("#inputStandort");
        var oF = $("#inputFile");
        var oO = $("#inputOBT");
        var oI = $("#inputImg");
        var $frmUpload = $('#fupForm');

        oO.on('change', function(e) {
            var lbl = $("label[for=inputOBT]").waitMe({ effect: 'ios' });
            if ( this.files.length > 0 && readImageFilenames) {
                readImageFilenames( this.files[0] ).then( function(matches) {
                    objektbuchImages = matches;
                }).finally( function() {
                    lbl.waitMe('hide');
                });
            }
        });

        for (var i = 0; i < aMandanten.length; i++) {
            var _m = aMandanten[i];
            oM.append(
                $("<option/>")
                    .val(_m.mid).text(_m.Mandant)
                    .prop("selected", activeMandant == _m.mid)
            );
        }
        oM.dropdown();

        $frmUpload
            .form({
                fields: {
                    mid: {
                        identifier: 'inputMandant',
                        rules: [
                            {
                                type: 'empty',
                                prompt: 'Bitte wählen Sie den Mndanten aus.'
                            }
                        ]
                    },
                    InventarDaten: {
                        identifier: 'inputFile',
                        rules: [
                            {
                                type: 'empty',
                                prompt: 'Es wurde noch keine Inventar-Dateie für den Upload gewählt!'
                            }
                        ]
                    }
                }
            });

        oI.change(function () {
            var ele = document.getElementById($(this).attr('id'));
            var result = ele.files;
            var fileSizeSum = 0;
            var fileSizeScaledSum = 0;

            var btnSubmit = getFormSubmitButton();

            btnSubmit.prop('disabled', true).waitMe({ effect: 'ios' });

            $("#fileList").html('');

            if (!result.length) {
                $("#fileTable").hide();
                return;
            } else {
                $("#fileTable").show();
            }

            uploadSelectedFiles = [];
            originSelectedFiles = [];
            numCheckedFiles = 0;
            checkedFilesIds = 0;
            $("#imagesCheckedChanged").trigger("change");
            var onClickshowFullImg = function(e) {
                var img = e.target;
                if (img && img.src && img.src !== imgPlaceholder.src) {
                    window.open(img.src);
                }
            };

            var abortImageProcess = function(file) {
                for(var loadingImg of loadingImages) {
                    if (loadingImg.file === file) {
                        loadingImg.abort();
                        break;
                    }
                }
                var rfi = readingFiles.indexOf(file);
                if (rfi !== - 1) {
                    readingFiles[rfi].abort();
                }
            };

            var isFileInObjektbuchImages = function(fileName) {
                var idx = objektbuchImages.indexOf(fileName);
                if (idx > -1) {
                    console.log('Bild ' +fileName + ' in objektbuchImages mit exakter Übereinstimmung an Index ' + idx + ' gefunden!');
                    return true;
                }

                var len = objektbuchImages.length;
                var pattern = new RegExp('\\b' + fileName + '$');

                for (var i = 0; i < len; i++) {
                    if (objektbuchImages[i].match(pattern)) {
                        console.log('Bild ' +fileName + ' in objektbuchImages mit Übereinstimmung des Bildnamen an Index ' + idx + ' gefunden!');
                        return true;
                    }
                }
                console.error('Bild ' +fileName + ' ist nicht in objektbuchImages enthalten');
                return false;
            };

            var appendRow = function (fle, id) {
                var row = $("<tr/>").attr('data-file', fle.name).data({inputFile: fle, inputFileIdx: i});
                var cellImage = $("<td data-label=\"image\"/>")
                    .append(
                        $("<img/>")
                            .attr('src', imgPlaceholder.src)
                            .css({maxWidth:'300px', maxHeight:'100px'})
                            .on('click', onClickshowFullImg)
                    );
                var cellFile = $("<td data-label=\"Datei\">" + fle.name + "</td>");
                var cellSize = $("<td class=\"right aligned\" data-label=\"Größe\">" + fileSizeReadable(fle.size) + "</td>");
                var iconClass = !objektbuchImages.length
                    ? 'file image'
                    : (!isFileInObjektbuchImages(fle.name) ? 'ban red' : 'check green');
                var cellIcon = $("<td class=\"center aligned\" data-label=\"Stat\"><i class=\"ui icon big " + iconClass + "\"></i></td>");
                var cellAct = $("<td class=\"right aligned\" data-label=\"Aktion\"/>").attr("id", "filestatus_" + i);
                var btnDel = $("<span style='cursor: pointer'><i class='times circle icon'></i></td></span>");

                row.append(cellImage).append(cellFile).append(cellSize).append(cellIcon).append(cellAct.append(btnDel));

                btnDel.on('click', function () {
                    var tr = $(this).closest('tr');
                    var inputFile = tr.data("inputFile");
                    var ix = uploadSelectedFiles.indexOf(inputFile);
                    if (ix > -1) {
                        uploadSelectedFiles.splice(ix, 1);
                    }

                    tr.remove();
                    $("#imagesSelectionChanged").trigger("change");
                });

                $("#fileList").append(row);

                return row;
            };


            var numImages = result.length;
            for (var i = 0; i < result.length; i++) {
                var fle = result[i];
                originSelectedFiles[i] = result[i];
                uploadSelectedFiles[i] = result[i];
                fileSizeSum += fle.size;
                var row = appendRow(fle, i);
                var imgCell = row.find("td[data-label=image]");
                var imgNode = imgCell.find("img:first")[0];
                $(imgNode).waitMe({ effect: 'ios' });

                (function(file, i, imgNode){
                    imgInputScale(file).then(function(rslt){
                        if (rslt && rslt.changed && rslt.file instanceof File ) {
                            uploadSelectedFiles[i] = rslt.file;
                            fileAsImgSrc(rslt.file, imgNode);
                            imgNode.title = "Optimiert für Upload to " +
                                rslt.width + 'x' + rslt.height + ', ' +
                                fileSizeReadable(rslt.file.size);
                        } else {
                            fileAsImgSrc(file, imgNode);
                        }
                        numCheckedFiles += 1;
                        $(imgNode).waitMe('hide');
                        $("#imagesCheckedChanged").trigger("change");
                        onProgressImgScalingCheck(numCheckedFiles, numImages);
                    });
                })(result[i], i, imgNode);
            }
            $("#fileSizeSum").text(fileSizeReadable(fileSizeSum));
            $("#imagesSelectionChanged").trigger("change");

        });

        $("#imagesSelectionChanged").on("change", function(e) {
            var filesCount = uploadSelectedFiles.length;
            var filesSize = uploadSelectedFiles.reduce(function(c,f){ return c + f.size;}, 0);
            var fileSizeFormatted = fileSizeReadable(filesSize);
            $(".input-box-images").find(".file-name").text(filesCount + " Dateien");
            $(".input-box-images").find(".file-size").text(fileSizeFormatted);
            $("#fileSizeSum").text(fileSizeFormatted);
        });

        oM
            .on("change", function (e) {
                activeMandant = parseInt($(this).val());

                oS.find("option:not([value=''])").remove();

                for (var si = 0; si < aStandorte.length; si++) {
                    var _s = aStandorte[si];
                    var _smid = parseInt(_s.mandanten_id);

                    if (activeMandant != _smid) {
                        continue;
                    }

                    oS.append(
                        $("<option/>")
                            .val(_s.gid).text(_s.Gebaeude)
                            .prop('selected', activeStandort == _s.gid)
                    );
                }
            })
            .trigger("change");

        $('label.upload + input[type=file]').on('change', function (e) {
            console.log('#309 label.upload + input[type=file] changed');
            var uplBox = $(this).closest('.segment');
            var fName = uplBox.find('.file-name');
            var popUp = uplBox.next(('.ui.modal'));

            if (popUp.length) {
                popUp.modal('hide');
                popUp.find('#filesContent').html('');
            }

            if (this.files.length < 1) {
                uplBox.find('.file-name').text('');
                uplBox.find('.file-size').text('');
            } else if (this.files.length === 1) {
                uplBox.find('.file-name').text(this.files[0].name);
                uplBox.find('.file-size').text(fileSizeReadable(this.files[0].size));
            } else {
                var files = [];
                for(var i = 0; i < this.files.length; i++) files.push( this.files.item(i) );

                var sumSize = files.reduce(
                    function(c, f){ return c + f.size;}, 0
                );

                uplBox.find('.file-name').text(files.length + " Dateien");
                uplBox.find('.file-size').text(fileSizeReadable( sumSize ));

                if (!popUp.length) {
                    popUp = $('<div class="ui basic modal">' +
                        '<i class="close icon"></i>' +
                        '<div class="header">Ausgewählte Dateien für den Upload</div>' +
                        '<div id="filesContent" class="content"></div>' +
                        '<div class="actions">' +
                        '  <div class="ui green ok inverted button">' +
                        '    <i class="checkmark icon"></i> OK' +
                        '  </div>' +
                        '</div>' +
                        '</div>')
                        .css({ minWidth: '50vw', maxWidth: '100vw' })
                        .insertAfter(uplBox);
                }

                popUp.find('#filesContent').html(
                    '<div class="autocol-2"><ol><li>' +
                    files.map(function(f){return f.name;}).join('</li><li>') +
                    '</li></ol></div>'
                );

                $("#imagesSelectionChanged").on("change", function(e) {
                    popUp.find('#filesContent').html(
                        '<div class="autocol-2"><ol><li>' +
                        uploadSelectedFiles.map(function(f){return f.name;}).join('</li><li>') +
                        '</li></ol></div>'
                    );
                });

                fName.on('click', function() { popUp.modal('show'); });

            }
        });

        oF.on("input", function (e) {
            const sInputFileName = $(this).val();
            if ($.trim(sInputFileName) === '') {
                return;
            }
            console.log({sInputFileName});

            e.preventDefault();
            $.ajax({
                type: 'GET',
                url: '/api/admin/import/checkInputFileName',
                data: {'inputFileName': sInputFileName},
                dataType: 'json',
                success: function (response) { //console.log(response);

                    if (response.status == 0 || ('success' in response && response.success === false)) {
                        $('.submitBtn').attr("disabled", "disabled").prop("disabled", true);
                        $('.statusMsg').html('').append(
                            $('<p class="ui negative message"/>').html( response.message.split("\n").join("<br>") )
                        );
                    } else {
                        $('.submitBtn').removeAttr("disabled").prop("disabled", false);
                        $('.statusMsg').html('');
                    }
                },
                error: function (xhr, statusText, thrownError) {
                    console.error({xhr, statusText, thrownError });

                    var errMsg = 'Server-Fehler<br>';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg+= xhr.responseJSON.message + '<br>\n';
                    } else {
                        if (xhr.status) {
                            errMsg += 'Status ' + xhr.status + ' ' + thrownError + '<br>\n';
                        } else if (thrownError) {
                            errMsg += thrownError;
                        }
                        if (statusText && statusText.toLowerCase() !== 'error') {
                            errMsg += "<span data-prop='statusText'>" + statusText + "</span><br>\n";
                        }
                        if (xhr.statusMessage) {
                            errMsg += "<span data-prop='xhr.statusMessage'>" + xhr.statusMessage + "</span><br>\n";
                        }
                    }

                    $('.submitBtn').removeAttr("disabled").prop("disabled", false);
                    $('.statusMsg').html('').append(
                        $('<p class="ui negative message"/>').html( errMsg.split("\n").join("<br>") )
                    );
                    if (xhr.status == 401) {
                        $('.statusMsg').append(
                            $('<p class="ui positive message"/>').append(
                                $("button")
                                    .addClass('ui button')
                                    .text('Zum Login mit Weiterleitung auf diese Seite')
                                    .on('click', function() { window.location.reload(); } )
                            )
                        );
                    }
                }
            });
        });

        function statusCallback(idx, statusTxt) {
            switch (statusTxt) {
                case 'load':
                    numLoadedFiles++;
                    numFinishedFiles++;
                    break;

                case 'error':
                    numErrorFiles++;
                    numFinishedFiles++;
                    break;

                case 'abort':
                    numAbortedFiles++;
                    numFinishedFiles++;
                    break;
            }
            var percent = 100 * numFinishedFiles / numUploadedFiles;
            setStepStatus(4, 'Bilder-Upload: ' + uploadSelectedFiles.length, percent );

            if (numFinishedFiles === numUploadedFiles) {
                setStepStatus(4, 'Finished: ' + numFinishedFiles.length, percent );
                $("#imagesDone").val('finished').trigger('change');
                console.log(
                    "Bilder-Übertragung wurde abgeschlossen\n" +
                    "Ausgewählte Dateien: " + numUploadedFiles + "\n" +
                    "Erfolgreich: " + numLoadedFiles + "\n" +
                    "Fehlerhafte: " + numErrorFiles + "\n" +
                    "Abgebrochen: " + numAbortedFiles + "\n"
                );
            }
        }

        function submitImages(jobid) {
            var url = '/api/admin/import/uploadImages/' + jobid;
            var KB = 1024;
            var maxImgFileSize = 100 * KB;
            numUploadedFiles = 0;
            numFinishedFiles = 0;
            numLoadedFiles = 0;
            numAbortedFiles = 0;
            numErrorFiles = 0;
            setStepStatus(4, 'Bilder-Upload: ' + uploadSelectedFiles.length, 0 );

            for (var i = 0; i < uploadSelectedFiles.length; i++) {
                var file = uploadSelectedFiles[i];
                var originIdx = originSelectedFiles.indexOf(file);
                uploadSingleFile(url, 'InventarImg[]', file, originIdx, statusCallback);
                numUploadedFiles++;
            }

        }

        $("#bntSubmitImages").on("click", function(e) {
            submitImages(6);
        });

        // Submit form data via Ajax
        // - Mandant
        // - Zip-File
        // - Objektbuch
        $("#fupForm").on('submit', function (e) {
            console.log('inline fupform on submit');
            e.preventDefault();

            $("#fupForm").form('validate form');

            if (!$("#fupForm").form('is valid')) {
                console.error('Cancelled submit in LINE #511! $("#fupForm").form(\'is valid\')', $("#fupForm").form('is valid'));
                return false;
            }

            var formData = new FormData();
            if (!$("#inputFile").get(0).files.length) {
                console.error('Cancelled submit in LINE #517! $("#inputFile").get(0).files.length', $("#inputFile").get(0).files.length);
                return;
            }

            formData.append('mid', $("#inputMandant").get(0).value);
            formData.append('gid', $("#inputStandort").get(0).value);
            formData.append('InventarDaten', $("#inputFile").get(0).files[0], $("#inputFile").get(0).files[0].name);

            if ($("#inputOBT").get(0).files.length > 0) {
                formData.append('InventarOBT', $("#inputOBT").get(0).files[0], $("#inputOBT").get(0).files[0].name);
            }

            $(this).waitMe({effect: 'progressBar', text: 'Bitte warten, Daten werden verarbeitet' });

            $.ajax({
                xhr: function() {
                    var xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', function(e) {
                        percentUpload = Math.floor(100 * e.loaded / e.total);
                        console.log('#310 onprogress', { e });

                        var percent = (e.loaded / e.total) * 100;
                        $("#progressFiles").find(".progress-status").text(Math.round(percent) + "% uploaded, please wait...");
                        $("#progressFiles").find('#progressbar').css("width", percent + "%");
                        $("#progressFiles").find("#notify").text("Uploaded " + fileSizeReadable(e.loaded) + " of " + fileSizeReadable(e.total));

                        var stepNr = setNextStep(1);
                        if (e.total > e.loaded) {
                            setStepStatus(stepNr, 'progress', percent);
                        } else {
                            setStepStatus(stepNr, 'complete', 100);
                            stepNr = setNextStep(2);
                            setStepStatus(stepNr, 'copy', -1);
                        }
                    });

                    return xhr;
                },
                type: 'POST',
                url: '/api/admin/import/upload',
                data: formData,
                dataType: 'json',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function (xhr, settings) {
                    setStepStatus(1, 'upload', 0);
                    $('.submitBtn').attr("disabled", "disabled");
                    $('#fupForm').css("opacity", ".5");
                },
                success: function (response) { //console.log(response);
                    $('.statusMsg').html('');
                    console.log({response});
                    if (response.status == 1) {
                        $('#fupForm')[0].reset();

                        if (!('jobid' in response) || !response.jobid) {
                            $('.statusMsg').append(
                                $('<p data-line="1015" class="ui negative message"/>')
                                    .html( 'Server-Antwort enthält keine Angabe zur angelegten Inventur!')
                            );
                            return;
                        }

                        jobid = response.jobid;
                        if (uploadSelectedFiles.length > 0) {
                            response.onSuccessAction = 'submitImages';
                            response.onSuccess = submitImages.bind(window, response.jobid);
                        }

                        nextResponseDialog(response, setNextStep(3));

                    } else {
                        $('.statusMsg').append(
                            $('<p data-line="1033" class="ui negative message"/>').html( $.trim(response.message).split("\n").join("<br>"))
                        );
                        $('#fupForm').waitMe('hide');
                    }
                },
                complete: function (xhr, status) {
                    $('#fupForm').css("opacity", "");
                    $(".submitBtn").removeAttr("disabled");
                    console.log('complete', {xhr, status});
                },
                error: function() {
                    console.log('Server-Fehler', { 'error': arguments });
                    var args = [].slice.apply(arguments, [0]);
                    var response = (args.length > 0) ? args[0] : 'Unbekannter Upload-Fehler!';
                    showResponseError(response);
                    setStepStatus(1, 'finished', 100);
                    $('#fupForm').waitMe('hide');
                }
            });
        });

        function importFinished(jobid) {
            $('#fupForm').waitMe('hide');
            if (!jobid) {
                return;
            }
            $('.statusMsg').prepend(
                $('<p data-line="1098" class="ui success message">')
                    .append( $('<p/>').text('Fertig!') )
                    .append(
                        $('<div class="ui button green"/>')
                            .text('Inventur-Verwaltung öffnen')
                            .on("click", function(e) {
                                window.location.href = '/api/admin/inventuren/' + jobid;
                            })
                    )
            );
        }

        function removeInvalidImages(validObjektbuchImages)
        {
            var aHelper = [];
            var aValidIds = [];
            var numRemoved = 0;

            for (var i = 0; i < validObjektbuchImages.length; i++) {
                aValidIds.push(validObjektbuchImages[i].ID);
            }
            console.log('Remove Upload-Image', { validObjektbuchImages, aValidIds });

            for (var j = 0; j < uploadSelectedFiles.length; j++) {
                var f = uploadSelectedFiles[j].name;
                var m = f.match(/(.*)?\.(jpg|jpeg|png|gif)$/);
                var sTest = (Array.isArray(m) && m.length > 0) ? m[1] : f;

                if (aValidIds.indexOf(sTest) > -1 || aValidIds.indexOf(f) > -1) {
                    aHelper.push(uploadSelectedFiles[j]);
                } else {
                    var selector = "tr[data-file=" + $.escapeSelector(f) + "]";
                    var found = $(selector).length;
                    $(selector).remove();
                    ++numRemoved;
                    console.log('Remove Upload-Image', { f, m, sTest, selector, found, numRemoved });
                }
            }
            if (numRemoved > 0) {
                uploadSelectedFiles = aHelper;
                $("#imagesSelectionChanged").trigger("change");
            }
        }

        function nextResponseDialog(response, nextStep) {
            console.log('#450 nextResponseDialog', { response, nextStep, lastStep });
            var rsp = response;
            var lnk = (rsp.nextLinkHref)
                ? $('<div style="display:none;">').append(
                    $('<a/>').attr({href: rsp.nextLinkHref}).text(rsp.nextLinkText || 'Weiter')
                )
                : '';

            if ('validObjektbuchImages' in response && Array.isArray(response.validObjektbuchImages)) {
                removeInvalidImages(response.validObjektbuchImages);
            }

            var msg = (rsp.message)
                ? $.trim(response.message).split("\n").join("<br>")
                : (lastStep ? 'Finished Step ' + lastStep : '');

            var nextLinkHref = ('nextLinkHref' in rsp && typeof rsp.nextLinkHref === 'string') ? rsp.nextLinkHref : '';
            var nextLinkText = ('nextLinkText' in rsp && typeof rsp.nextLinkText === 'string') ? rsp.nextLinkText : '';

            if (lastStep) {
                setStepStatus(lastStep, 'finished', 100);
            }

            if (rsp.success || ('status' in rsp && rsp.status == 1)) {
                $('.statusMsg').prepend(
                    $('<p data-line="1070" class="ui success message">')
                        .html(msg)
                        .append(lnk)
                );
            } else {
                if (rsp.error) {
                    msg += "<br>\n" + $.trim(rsp.error).split("\n").join("<br>");
                }
                $('.statusMsg').prepend(
                    $('<p data-line="1077" class="ui negative message"/>')
                        .html(msg)
                        .append(lnk)
                );
            }


            if (nextLinkHref.match(/imageimport/) && numUploadedFiles === 0) {

                if (uploadSelectedFiles.length > 0) {
                    setStepStatus(nextStep, 'loading', 0);
                    submitImages(jobid);

                    $("#imagesDone").on('change', function (e) {
                        setStepStatus(nextStep, 'completed', 100);
                        var stepNr = setNextStep(5);
                        var nextResponse = {
                            success: true,
                            message: '',
                            error: '',
                            nextLinkHref,
                            nextLinkText,
                        };
                        // Vermeide Endlos-Schleife, in dem sichergestellt wird
                        // dass numUploadedFiles nicht wiederholt 0 ist
                        if (numUploadedFiles === 0) {
                            numUploadedFiles = -1;
                        }
                        nextResponseDialog(nextResponse, stepNr);
                    });
                } else {
                    setStepStatus(nextStep, 'finished', 0);
                    importFinished(jobid);
                }
            } else if (nextLinkHref !== '') {
                setStepStatus(nextStep, 'loading', 0);
                $.get(rsp.nextLinkHref, {}, function (data) {
                    setStepStatus(nextStep, 'finished', 100);
                    nextResponseDialog(data, getNextStep());
                }).fail( function() {
                    showResponseError(arguments[0]);
                });
            } else {
                importFinished(jobid);
            }
        }
    });

</script>

@endsection
