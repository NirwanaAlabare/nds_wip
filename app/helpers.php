<?php
    function is_decimal($val)
    {
        return is_numeric( $val ) && floor( $val ) != $val;
    }

    function curr($num)
    {
        if (is_numeric($num)) {
            if (is_decimal($num)) {
                $hasil_rupiah = number_format($num,2,',','.');
            } else {
                $hasil_rupiah = number_format($num, 0, ',', '.');
            }

            $hasil_rupiah = number_format($num,2,',','.');

            return $hasil_rupiah;
        }

        return 0;
    }

    function num($num, $dec = 0)
    {
        if (is_numeric($num)) {
            $hasil = 0;

            if (is_decimal($num)) {
                if ($dec == 0) {
                    $dec = 2;
                }
            }

            $hasil = number_format($num, $dec, ',', '.');

            return $hasil;
        }

        return 0;
    }

    function localeDateFormat($date){
        $day = date("D", strtotime($date));

        $localeDay = "";
        switch($day){
            case 'Sun':
                $localeDay = "Minggu";
                break;
            case 'Mon':
                $localeDay = "Senin";
                break;
            case 'Tue':
                $localeDay = "Selasa";
                break;
            case 'Wed':
                $localeDay = "Rabu";
                break;
            case 'Thu':
                $localeDay = "Kamis";
                break;
            case 'Fri':
                $localeDay = "Jumat";
                break;
            case 'Sat':
                $localeDay = "Sabtu";
                break;
            default:
                $localeDay = "-";
                break;
        }

        $month = array (
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );

        $dateExplode = explode('-', $date);

        return $localeDay. ', ' . $dateExplode[2] . ' ' . $month[ (int) $dateExplode[1] ] . ' ' . $dateExplode[0];
    }

    function getDatesFromRange($start, $end, $format = 'Y-m-d'){

        // Declare an empty array
        $array = array();

        // Variable that store the date interval
        // of period 1 day
        $interval = new DateInterval('P1D');
        $realEnd = new DateTime($end);
        $realEnd->add($interval);
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);

        // Use loop to store date into array
        foreach($period as $date){
            $array[] = $date->format($format);

        }

        // Return the array elements
        return $array;
    }
?>
