function ferfiSzures() {
    location.href = '/ferfiOldal/kategoria/' + document.getElementById('kat_id').value;
}

function noiSzures() {
    location.href = '/noiOldal/kategoria/' + document.getElementById('kat_id').value;
}

function gyermekSzures() {
    location.href = '/gyermekOldal/kategoria/' + document.getElementById('kat_id').value;
}

function adminNemKategoriaMarkaSzures() {
    location.href = 'index.php?nemId=' + document.getElementById('nem').value + '&kategoriaId=' + document.getElementById('kategoria').value + '&markaId=' + document.getElementById('marka').value;
}

function adminFelhasznaloSzures() {
    location.href = 'index.php?jogosultsagId=' + document.getElementById('jogosultsag').value;
}

function adminLogKategoriaSzures() {
    location.href = 'index.php?logCategoryId=' + document.getElementById('kategoria').value;
}

$(document).ready(function() {
    //alert( "ready!" );
    kosarAdatai();
    $('input.kosarmennyiseg').change(function() {

        var m = $(this).val();
        var ar = $(this).attr('ear');
        var ujar = m * ar;
        var s = ujar.toLocaleString();
        $(this).next().html(m + ' db');
        $(this).next().next().html(s + ' Ft');

        mennyisegModositas($(this).attr('formid'));

    });
});

function kosarAdatai() {
    $.post(
            '/App/Controller/cartGet.php'
        )
        .done(function(data, statusz) {
            if (statusz == "success") {
                var r = JSON.parse(data);
                if (r['kod'] == 0 && r['adat'].tetel > 0) {
                    //$('#kosarOsszegTmpStr').html(r['adat'].osszeg+' Ft');
                    //$('#kosarOsszegTmpNum').html(r['adat'].osszegNum);
                    $('#kosarOsszeg').html(r['adat'].osszeg + ' Ft');
                    $('a.kosar').attr('title', r['adat'].tetel + ' db tétel, értéke: ' + r['adat'].osszeg + ' Ft');
                    $('a.kosar span').attr('title', r['adat'].tetel + ' db tétel, értéke: ' + r['adat'].osszeg + ' Ft');
                    $('a.kosar span').html(r['adat'].tetel);
                } else {
                    kosarUres();
                }
            } else {
                kosarUres();
            }
        })
        .fail(function(adatok, statusz) {
            kosarUres();
        });
}

function kosarUres() {
    $('a.kosar').attr('title', '');
    $('a.kosar span').attr('title', '');
    $('a.kosar span').html('');
    $('#kosarOsszeg').html('');
}