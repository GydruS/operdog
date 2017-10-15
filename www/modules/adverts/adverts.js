function deleteRecord(id){
    doConfirm('Удалить объявление #'+id+'?', function(confirmed){
        if (confirmed) {
            var url = siteRoot+'adverts/delete/'+id;
            openUrl(url);
        }
    });
    return false;
}

function deleteImage(imgId, itemId){
    doConfirm('Удалить фото?', function(confirmed){
        if (confirmed) {
            //var url = siteRoot+'adverts/edit/'+itemId+'/?action=deleteImage&id='+imgId;
            var url = siteRoot+'adverts/deleteImage/'+imgId;
            openUrl(url);
        }
    });
    return false;
}

$(document).on('change', ':file', function() {
    var input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
    input.trigger('fileselect', [numFiles, label]);
});

$(document).ready(function(){
    $(':file').on('fileselect', function(event, numFiles, label) {
        $('#fileName').val(label);
    });

    $('.clockpicker').clockpicker({'default': 'now'});
    //$('#eventTime').val(timeStr);
    $('#eventTime').mask('99:99');
    
    $('#eventDate').datepicker({
        autoclose: true,
        language: 'ru',
        todayBtn: 'linked',
        todayHighlight: true,
    });
});
