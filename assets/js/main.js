function calcularRecambios() {
    var totalInfusion = 0;
    var totalDrenaje = 0;

    for (var i = 1; i <= 4; i++) {
        var infusion = 2000;
        var drenajeInput = document.getElementById("drenaje" + i);
        var drenaje = parseInt(drenajeInput.value);

        if (isNaN(drenaje)) {
            drenaje = 0;
        }

        var balance = infusion - drenaje;
        document.getElementById("balance" + i).innerHTML = balance;

        totalInfusion = totalInfusion + infusion;
        totalDrenaje = totalDrenaje + drenaje;
    }

    var balanceFinal = totalInfusion - totalDrenaje;

    document.getElementById("totalInfusion").innerHTML = totalInfusion;
    document.getElementById("totalDrenaje").innerHTML = totalDrenaje;
    document.getElementById("balanceFinal").innerHTML = balanceFinal;
}
