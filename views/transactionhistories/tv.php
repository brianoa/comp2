<?php
?>
<style>
</style>
<div class="row">
    <div id="numbers" style="display: none;"><?= $data; ?></div>
    <p class="col text-center" style="margin:10%;font-weight:bold;font-size:150px;" id="counter">
        </h1>
</div>
<div class="row text-center">
    <div class="col-md-4"></div>
    <div class="col-md-4">
        <button id="draw_button" type="button" class="btn btn-light btn-lg" onclick="drawTv('<?= $show_id; ?>','<?= $presenter_id; ?>','<?= $prize_id; ?>','<?= $from; ?>',2,'<?= $to; ?>')"><b>DRAW</b></button>
    </div>
    <div class="col-md-4"></div>
</div>
<script>
    const numbers = <?= $data; ?>;
    counter = document.getElementById("counter");
    counts = setInterval(updated);
    let i = 0;

    function updated() {
        let raw_num = numbers[++i];
        //let formated_num = raw_num.substring(3);
        counter.innerHTML = raw_num;
        if (i == numbers.length - 1) {
            i = 0;
        }

    }

    function pickWinner() {
        clearInterval(counts);
        //counter.innerHTML='254728202194';
    }

    function drawTv(station_show_id, presenter_id, prize_id, from, admin_draw, to) {

        $.post(host + '/winninghistories/draw', {
            station_show_id: station_show_id,
            presenter_id: presenter_id,
            prize_id: prize_id,
            from: from,
            admin_draw: admin_draw,
            to: to
        }, function(data) {
            var data = JSON.parse(data);
            if (data.status == "fail") {
                clearInterval(counts);
                counter.innerHTML = "0 0 0 0 0 0 0 0 0 0 0 0";
            } else {
                clearInterval(counts);
                let formated_winner = data.data.reference_phone.substring(0, 9);
                counter.innerHTML = formated_winner + '***';

            }
            //console.log( typeof data)
            console.log(data)
        })
        //console.log("You clicked "+presenter_id)

    }
</script>