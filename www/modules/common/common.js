function empty(val) {
    return ((typeof(val) === 'undefined') || (val === '') || (val === 0) || (val === null) || (val === false) || (Array.isArray && Array.isArray(val) && val.length == 0));
}

var generatePassword = function(length, ranges) {
    var symRanges = ranges || ['A-Z', 'a-z', '0-9'],
            symbols = [],
            i, n,
            pass = '';
    length = length || 10; // длина пароля
    for (i = 0, n = symRanges.length; i < n; ++i)
    {
        var range = symRanges[i].split('-');
        if (range.length == 2)
        {
            var stCode = range[0].charCodeAt(0),
                    endCode = range[1].charCodeAt(0),
                    tmp;
            if (stCode > endCode)
            {
                tmp = stCode;
                stCode = endCode;
                endCode = tmp;
            }

            for (var j = stCode, k = endCode; j <= k; ++j)
            {
                symbols.push(String.fromCharCode(j));
            }
        }
        else
        {
            symbols.push(range[0]);
        }
    }

    symbols = symbols.join('');
    for (i = 0; i < length; ++i)
    {
        pass += symbols.charAt(~~(Math.random() * symbols.length));
    }

    return pass;
};

function in_array(needle, haystack) {
    return haystack.indexOf(needle) != -1;
}

function is_numeric( mixed_var ) {
    return ( mixed_var == '' ) ? false : !isNaN( mixed_var );
}

function getRootDir(){
    return window.location.protocol+'//'+window.location.host+'/'+ROOT_DIR;
}

function log(){
    var prefix = (!empty(this.name)) ? this.name+': ' : '';
    for (var i=0; i<arguments.length; i++) {
        if (!empty(prefix)) console.log(prefix+arguments[i]);
        else console.log(arguments[i]);
    }
}

function logCallStack() {
    var stack = new Error().stack;
    log(stack);
}

function openUrl(url) {
    window.location.href = url;
}

function displayErrors(result, advMessage) {
    advMessage = advMessage || '';
    var errorMsgs = '<h4 class="text-danger">Errors: <br/></h4>';
    $(result.errors).each(function(index,elem){
        errorMsgs += elem.message+'<br/>';
    });
    showAlert(errorMsgs+advMessage);
}

function showAlert(text, callback) {
    callback = callback || false;
    if (!callback) {
        bootbox.hideAll();
        bootbox.alert(text);
    }
    else {
        var okBtn = {label: "Ok", className: "btn-primary", callback:callback};
        var buttons = {ok:okBtn};
        customAlert('', text, buttons);
    }
}

function doConfirm(message, callback) {
    //bootbox.confirm(message, callback);
    bootbox.confirm(
        {
            message: message,
            buttons: {
                confirm: {
                    label: 'Да',
                    className: 'btn-danger'
                },
                cancel: {
                    label: 'Нет',
                    className: 'btn-primary'
                }
            },
            callback: callback
        }
    );
}

function customAlert(title, message, buttons) {
    bootbox.hideAll();
    var dlgCfg = {
        message: message,
        buttons: buttons
    };
    if (!empty(title)) dlgCfg.title = title;
    bootbox.dialog(dlgCfg);
}

function objectFieldsCount(obj) {
    if (obj.__count__ !== undefined) { // Old FF
        return obj.__count__;
    }

    if (Object.keys) { // ES5 
        return Object.keys(obj).length;
    }

    // Everything else:
    var c = 0, p;
    for (p in obj) {
        if (obj.hasOwnProperty(p)) {
            c += 1;
        }
    }

    return c;
}

function formatBytes(bytes,decimals) {
   if(bytes == 0) return '0 Bytes';
   var k = 1000,
       dm = decimals + 1 || 2,
       sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
       i = Math.floor(Math.log(bytes) / Math.log(k));
   return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function checkFileExists(url, successCallback, failCallback) {
    successCallback = successCallback || null;
    failCallback = failCallback || null;
    $.ajax({
        url:url,
        type:'HEAD',
        error: function() {
            //file not exists
            if (!empty(failCallback)) failCallback(url);
        },
        success: function() {
            //file exists
            if (!empty(successCallback)) successCallback(url);
        }
    });
}

function loginDialog() {
    var buttons = {
        ok: {
            label: "Sign in",
            className: "btn-primary",
            callback: function () {
                var login = $('#loginDialogLogin').val();
                var password = $('#loginDialogPassword').val();
                
                var res = $.ajax({
                    type: 'GET',
                    url: getRootDir()+'auth/login',
                    method: 'post',
                    cache: false,
                    data: {login:login, password:password},
                    async: false
                }).responseText;
                res = $.parseJSON(res);
                log(res);
                if (!res.result) {
                    var errorMsgs = '';
                    $(res.errors).each(function(index,elem){
                        errorMsgs += elem.message+'<br/>';
                    });                    
                    $('#loginErrorMessage #errorText').html(errorMsgs);
                    $('#loginErrorMessage').show();
                    return false;
                }
                else {
                    var a = window.location.href.split('#');
                    var url = a[0];
                    openUrl(url); //window.location.reload();
                }
            }
        },
        cancel: {label: "Cancel", className: "btn btn-default"},
    };

    var loginFieldsHtml = 
        '<form class="form-horizontal" action="/{$siteRoot}{/document/dictionaryInfo/moduleName}/" method="post" enctype="multipart/form-data" accept-charset="utf-8">\n\
            <div class="form-group">\n\
                <div class="row">\n\
                    <label class="control-label col-xs-3">Login</label>\n\
                    <div class="col-xs-7">\n\
                        <input type="text" id="loginDialogLogin" class="form-control" name="login" value="" style="width: 100%;"/>\n\
                    </div>\n\
                </div>\n\
            </div>\n\
            <div class="form-group">\n\
                <div class="row">\n\
                    <label class="control-label col-xs-3">Password</label>\n\
                    <div class="col-xs-7">\n\
                        <input type="password" id="loginDialogPassword" class="form-control" name="login" value="" style="width: 100%;"/>\n\
                    </div>\n\
                </div>\n\
            </div>\n\
        </form>\n\
        <div class="row" id="loginErrorMessage" style="display: none;">\n\
            <div class="col-xs-12 text-center">\n\
                <span class="text-danger" id="errorText"/>\n\
            </div>\n\
        </div>\n\
        ';

    customAlert('Sign in', loginFieldsHtml, buttons);
}

function showMap(address) {
    
    /*var buttons = {
        cancel: {label: "Закрыть", className: "btn btn-primary"},
    };

    var html = 
        '<iframe width="500" height="500" src = "https://maps.google.com/?q='+address+'">\n\
        </iframe>\n\
    ';

    customAlert('Карта', html, buttons);*/
    
    openUrl('https://maps.google.com/?q='+address);
}

function alertIfAdBlocked() {
    var a = document.getElementsByClassName('adverts');
    if ( a[0] && a[0].clientHeight == 0 ) { 
        showAlert("Обнаружен плагин Adblock, который ошибочно блокирует данный сайт! Пожалуйста, добавьте его в белый список плагина, и презагрузите страницу!");
    }
}

function fixEmails() {
    $('.email').each(function(i,e){
        var email = $(this).html().replace('[a@t]', '@');
        $(this).html('<a href="mailto:'+email+'">'+email+'</a>');
    });
}
    
$(document).ready(function(){
    $('.loginDialog').click(loginDialog);
});
