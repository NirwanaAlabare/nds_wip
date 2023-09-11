@extends('layouts.index')

@section('content')
    <div class="card card-sb card-outline">
        <div class="card-header">
            <h5 class="card-title">Timer</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-3 mb-3">
                <div class="d-flex gap-1">
                    <input type="text" class="form-control form-control-sm" id="minutes" value="00" readonly class="mx-1">
                    :
                    <input type="text" class="form-control form-control-sm" id="seconds" value="00" readonly class="mx-1">
                </div>
                <button type="button" class="btn btn-success btn-sm" id="startLapButton" onclick="startTimeRecord()">Start</button>
                <button type="button" class="btn btn-primary btn-sm" id="nextLapButton" onclick="addNewTimeRecord()">Next Lap</button>
                <button type="button" class="btn btn-warning btn-sm" id="pauseLapButton" onclick="pauseTimeRecord()">Pause</button>
                <button type="button" class="btn btn-danger btn-sm" id="stopLapButton" onclick="stopTimeRecord()">Stop</button>
            </div>
            <table class="table" id="timeRecordTable">
                <thead>
                    <tr>
                        <th>Lap</th>
                        <th>Waktu</th>
                        <th class="d-none"></th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@section('custom-script')
    <script>
        var startLapButton = document.getElementById("startLapButton");
        var pauseLapButton = document.getElementById("pauseLapButton");
        var stopLapButton = document.getElementById("stopLapButton");
        var nextLapButton = document.getElementById("nextLapButton");

        var minutes = document.getElementById("minutes");
        var seconds = document.getElementById("seconds");

        var timeRecordTable = document.getElementById('timeRecordTable');

        var lap = 0;
        var totalSeconds = 0;
        var timeRecordInterval = 0;

        seconds.value = pad(totalSeconds % 60);
        minutes.value = pad(parseInt(totalSeconds / 60));

        startLapButton.focus()

        function pad(val) {
            var valString = val + "";
            if (valString.length < 2) {
                return "0" + valString;
            } else {
                return valString;
            }
        }

        function setTime() {
            ++totalSeconds;
            seconds.value = pad(totalSeconds % 60);
            minutes.value = pad(parseInt(totalSeconds / 60));
        }

        function startTimeRecord() {
            timeRecordInterval = setInterval(setTime, 1000);

            pauseLapButton.removeAttribute("disabled");
            startLapButton.setAttribute("disabled", true);
            nextLapButton.focus();
        }

        function pauseTimeRecord() {
            clearTimeout(timeRecordInterval);

            pauseLapButton.setAttribute("disabled", true);
            startLapButton.removeAttribute("disabled");
            startLapButton.focus();
        }

        function stopTimeRecord() {
            clearTimeout(timeRecordInterval);
            totalSeconds = 0;
            timeRecordInterval = 0;

            seconds.value = pad(totalSeconds % 60);
            minutes.value = pad(parseInt(totalSeconds / 60));

            startLapButton.removeAttribute("disabled");
            startLapButton.focus();
        }

        function addNewTimeRecord() {
            totalSeconds = 0;

            lap++;

            let tbody = document.createElement('tbody');
            let tr = document.createElement('tr');
            let td1 = document.createElement('td');
            let td2 = document.createElement('td');
            let td3 = document.createElement('td');
            td1.innerHTML = lap;
            td2.innerHTML = minutes.value+' : '+seconds.value;
            td3.classList.add('d-none');
            td3.innerHTML = `<input type='hidden' name="timeRecord[`+lap+`]" value="`+minutes.value+':'+seconds.value+`" />`;
            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td3);

            timeRecordTable.appendChild(tr);
        }

        $(document).keyup(function(e) {
            if (e.key === "Backspace") {
                pauseTimeRecord()
            }

            if (e.key === "Escape") {
                stopTimeRecord()
            }
        });
    </script>
@endsection
