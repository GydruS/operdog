$(document).ready(function(){
    
    $('#js_btn_generate').click(function(){
        var psw = generatePassword(6);
        $('#js_fld_visible_password').val(psw);
        $('#js_visible_pwd').show();
        $('#js_fld_password1').val(psw);
        $('#js_fld_password2').val(psw);
        $('#js_pwd').hide();
        $('#js_pwd2').hide();
    });
    
    $('#reloadKaptcha').click(function(){
        var kaptcha = $('#kaptcha');
        var oldSrc = kaptcha.attr("src");
        kaptcha.attr("src", oldSrc+'&'+new Date().getTime());
    });
});