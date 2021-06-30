aMandanten = aMandanten || [];
aStandorte = aStandorte || [];

var activeMandant = '';
var activeStandort = '';
var originSelectedFiles = [];
var uploadSelectedFiles = [];
var numUploadedFiles = 0;
var numFinishedFiles = 0;
var numLoadedFiles = 0;
var numAbortedFiles = 0;
var numErrorFiles = 0;

function fileSizeReadable(fsize) {
    if (fsize < 1024) {
        return fsize + ' Bytes';
    } else if (fsize < (1024 *1024) ) {
        return (fsize / 1024).toFixed(1).toString().replace('.', ',') + ' KB';
    }
    return (fsize / (1024 * 1024)).toFixed(1).replace('.', ',') + ' MB';
}

function uploadSingleFile(url, inputName, file, i, statusCallback) {
    var fileId = i;
    var ajax = new XMLHttpRequest();

    $("#filestatus_" + i).append(
        '<div class="col-md-12">' +
        '<div class="progress-bar progress-bar-striped active" id="progressbar_' + fileId + '" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;height:0.5rem"></div>' +
        '</div>' +
        '<div class="col-md-12">' +
        '<div class="col-md-6">' +
        '<input type="button" class="btn btn-danger" style="display:none;line-height:6px;height:25px" id="cancel_' + fileId + '" value="cancel">' +
        '</div>' +
        '<div class="col-md-6">' +
        '<p class="progress-status" style="text-align: right;margin-right:-15px;font-weight:bold;color:saddlebrown" id="status_' + fileId + '"></p>' +
        '</div>' +
        '</div>' +
        '<div class="col-md-12">' +
        '<p id="notify_' + fileId + '" style="text-align: right;"></p>' +
        '</div>');

    //Progress Listener
    ajax.upload.addEventListener("progress", function (e) {
        var percent = (e.loaded / e.total) * 100;
        $("#status_" + fileId).text(Math.round(percent) + "% uploaded, please wait...");
        $('#progressbar_' + fileId).css("width", percent + "%")
        $("#notify_" + fileId).text("Uploaded " + fileSizeReadable(e.loaded) + " of " + fileSizeReadable(e.total));

    }, false);

    //Load Listener
    ajax.addEventListener("load", function (e) {
        $("#status_" + fileId).text('Upload Finished!');
        $('#progressbar_' + fileId).css("width", "100%");
        // progressCallback(i, file.size );

        //Hide cancel button
        var _cancel = $('#cancel_' + fileId);
        _cancel.hide();
        statusCallback(i, 'load' );
    }, false);

    //Error Listener
    ajax.addEventListener("error", function (e) {
        $("#status_" + fileId).text("Upload Failed");
        statusCallback(i, 'error' );
    }, false);

    //Abort Listener
    ajax.addEventListener("abort", function (e) {
        $("#status_" + fileId).text("Upload Aborted");
        statusCallback(i, 'abort' );
    }, false);

    ajax.open("POST", url); // Your API .net, php

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

$(function() {

    var oM = $("#inputMandant");
    var oS = $("#inputStandort");
    var oF = $("#inputFile");
    var oO = $("#inputOBT");
    var oI = $("#inputImg");

    for(var i = 0; i  < aMandanten.length; i++) {
        var _m = aMandanten[i];
        oM.append(
            $("<option/>")
                .val( _m.mid ).text( _m.Mandant )
                .prop( "selected", activeMandant == _m.mid)
        );
    }

    oI.change(function() {
        var ele = document.getElementById($(this).attr('id'));
        var result = ele.files;
        var fileSizeSum = 0;

        $("#fileList").html('');

        if (!result.length) {
            $("#fileTable").hide();
            return;
        } else {
            $("#fileTable").show();
        }

        uploadSelectedFiles = [];
        originSelectedFiles = [];

        var appendRow = function(fle, id) {
            var row = $("<tr/>").data({ inputFile: fle, inputFileIdx: i });
            var cellFile = $("<td data-label=\"Datei\">" + fle.name + "</td>");
            var cellSize = $("<td class=\"right aligned\" data-label=\"Größe\">" + fileSizeReadable(fle.size) + "</td>");
            var cellAct = $("<td class=\"right aligned\" data-label=\"Aktion\"/>").attr("id", "filestatus_" + i);
            var btnDel = $("<span style='cursor: pointer'><i class='times circle icon'></i></td></span>");

            row.append(cellFile).append(cellSize).append( cellAct.append( btnDel ) );

            btnDel.on( 'click', function() {
                var tr = $(this).closest('tr');
                var inputFile = tr.data("inputFile");
                var ix = uploadSelectedFiles.indexOf(inputFile);
                if (ix > -1) {
                    uploadSelectedFiles.splice(ix, 1);
                }
                alert( 'click ' + "\n" + tr.html());
                tr.remove();
            });

            $("#fileList").append( row );
        };


        for(var i = 0; i < result.length; i++) {
            var fle = result[i];
            originSelectedFiles[i] = result[i];
            uploadSelectedFiles[i] = result[i];
            fileSizeSum+= fle.size;
            appendRow(fle, i);


        }
        $("#fileSizeSum").text( fileSizeReadable(fileSizeSum));

    });

    oM
        .on("change", function(e) {
            activeMandant = parseInt( $(this).val() );

            oS.find("option:not([value=''])").remove();

            for(var si = 0; si < aStandorte.length; si++ ) {
                var _s = aStandorte[ si ];
                var _smid = parseInt( _s.mandanten_id );

                if (activeMandant != _smid) {
                    continue;
                }

                oS.append(
                    $("<option/>")
                        .val( _s.gid ).text( _s.Gebaeude )
                        .prop( 'selected', activeStandort == _s.gid )
                );
            }
        })
        .trigger( "change" );

    oF.on("input", function(e) {
        const sInputFileName = $(this).val();
        if ( $.trim( sInputFileName ) === '') {
            return;
        }
        console.log({ sInputFileName });

        e.preventDefault();
        $.ajax({
            type: 'GET',
            url: '/api/admin/import/checkInputFileName',
            data: { 'inputFileName': sInputFileName },
            dataType: 'json',
            success: function(response){ //console.log(response);

                if(response.status == 0){
                    $('.submitBtn').attr("disabled","disabled").prop("disabled", true);
                    $('.statusMsg').html('<p class="alert alert-danger">'+response.message+'</p>');
                }else{
                    $('.submitBtn').removeAttr("disabled").prop("disabled", false);
                    $('.statusMsg').html('');
                }
            }
        });
    });

    function statusCallback(idx, statusTxt) {
        switch(statusTxt) {
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

        if (numFinishedFiles === numUploadedFiles) {
            alert(
                "Dateiübertragung wurde abgeschlossen\n" +
                "Ausgewählte Dateien: " + numUploadedFiles + "\n" +
                "Erfolgreich: " + numLoadedFiles + "\n" +
                "Fehlerhafte: " + numLoadedFiles + "\n" +
                "Abgebrochen: " + numAbortedFiles + "\n"
            )
        }
    }

    function submitImages(jobid) {
        var url = '/api/admin/import/uploadImages/' + jobid;
        numUploadedFiles = 0;
        numFinishedFiles = 0;
        numLoadedFiles = 0;
        numAbortedFiles = 0;
        numErrorFiles = 0;

        for(var i = 0; i < uploadSelectedFiles.length; i++) {
            var file = uploadSelectedFiles[i];
            var originIdx = originSelectedFiles.indexOf( file );
            uploadSingleFile( url, 'InventarImg[]', file, originIdx, statusCallback );
            numUploadedFiles++;
        }

    }

    // Submit form data via Ajax
    $("#fupForm").on('submit', function(e)
    {
        console.log('myInventarUploader.js fupform on submit');
        e.preventDefault();

        var formData = new FormData();
        if (!$("#inputFile").get(0).files.length) {
            return;
        }
        if (!$("#inputOBT").get(0).files.length) {
            return;
        }
        formData.append('mid', $("#inputMandant").get(0).value);
        formData.append('gid', $("#inputStandort").get(0).value);
        formData.append('InventarDaten', $("#inputFile").get(0).files[0], $("#inputFile").get(0).files[0].name);
        formData.append('InventarOBT',$("#inputOBT").get(0).files[0], $("#inputOBT").get(0).files[0].name);

        $.ajax({
            type: 'POST',
            url: '/api/admin/import/upload',
            data: formData,
            dataType: 'json',
            contentType: false,
            cache: false,
            processData:false,
            beforeSend: function(){
                $('.submitBtn').attr("disabled","disabled");
                $('#fupForm').css("opacity",".5");
            },
            success: function(response){ //console.log(response);
                $('.statusMsg').html('');
                console.log({ response });
                if(response.status == 1){
                    $('#fupForm')[0].reset();
                    $('.statusMsg').html('<p class="alert alert-success">'+response.message+'</p>');

                    if (response.jobid) {
                        submitImages(response.jobid);
                    }
                }else{
                    $('.statusMsg').html('<p class="alert alert-danger">'+response.message+'</p>');
                }
            },
            complete: function(xhr, status) {
                $('#fupForm').css("opacity","");
                $(".submitBtn").removeAttr("disabled");
                console.log('complete', { xhr, status });
            }
        });
    });

});
