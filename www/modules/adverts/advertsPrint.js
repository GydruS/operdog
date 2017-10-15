function onLoad() {
    var id = 0;
    log(advertLink);
    $('.qrcode').each(function(id,el){
        new QRCode($(el).get(0), {
            text: advertLink,
            width: 128,
            height: 128,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    });
    printpage();
}