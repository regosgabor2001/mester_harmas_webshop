function termekEladas() {
    var januar = document.getElementById("januar").value;
    var februar = document.getElementById("februar").value;
    var marcius = document.getElementById("marcius").value;
    var aprilis = document.getElementById("aprilis").value;
    var majus = document.getElementById("majus").value;
    var junius = document.getElementById("junius").value;
    var julius = document.getElementById("julius").value;
    var augusztus = document.getElementById("augusztus").value;
    var szeptember = document.getElementById("szeptember").value;
    var oktober = document.getElementById("oktober").value;
    var november = document.getElementById("november").value;
    var december = document.getElementById("december").value;

    var xValues = ["január","február","március","április","május","június","július","augusztus","szeptember","október","november","december"];
    var yValues = [januar,februar,marcius,aprilis,majus,junius,julius,augusztus,szeptember,oktober,november,december];

    new Chart("termekChart", {
    type: "line",
    data: {
        labels: xValues,
        datasets: [{
        fill: false,
        lineTension: 0,
        backgroundColor: "rgba(0,0,255,1.0)",
        borderColor: "rgba(0,0,255,0.1)",
        data: yValues
        }]
    },
    options: {
        legend: {display: false},
        scales: {
        yAxes: [{ticks: {min: 0, precision: 0}}],
        }
    }
    });
}

function regisztraciok() {
    var admin = document.getElementById("admin").value;
    var vasarlo = document.getElementById("vasarlo").value;

    var xValues = ["Admin","Vásárló"];
    var yValues = [admin,vasarlo];

    new Chart("regisztracioChart", {
    type: "bar",
    data: {
        labels: xValues,
        datasets: [{
        fill: false,
        lineTension: 0,
        backgroundColor: [
            'rgba(255, 99, 132, 0.2)',
            'rgba(255, 159, 64, 0.2)',
          ],
          borderColor: [
            'rgb(255, 99, 132)',
            'rgb(255, 159, 64)',
          ],
          borderWidth: 1,
        data: yValues
        }]
    },
    options: {
        legend: {display: false},
        scales: {
        yAxes: [{ticks: {min: 0, precision: 0}}],
        }
    }
    });
}