function kosarbaTesz(id) {
    //alert("Oké");
    //console.log($('#formKosar').serialize());
    $.post(
        '/App/Controller/cartAdd.php',
        $('#'+id).serialize()
    )
    .done(function(data,statusz) {
        if(statusz=="success"){
            var r=JSON.parse(data);
            if (r['kod']==0) {
                $('a.kosar').attr('title',r['adat'].tetel+' db tétel, értéke: '+r['adat'].osszeg+' Ft');
                $('a.kosar span').attr('title',r['adat'].tetel+' db tétel, értéke: '+r['adat'].osszeg+' Ft');
                $('a.kosar span').html(r['adat'].tetel);
                //$('#kosarOsszegTmpNum').html(r['adat'].osszegNum);
                //$('#kosarOsszegTmpStr').html(r['adat'].osszeg+' Ft');
                $('#kosarOsszeg').html(r['adat'].osszeg+' Ft');
            }else{
                alert("Hiba");
            }
        }else{
            alert("Hiba");
        }
    })
    .fail(function(adatok, statusz){
        alert("Hiba");
    });
}

