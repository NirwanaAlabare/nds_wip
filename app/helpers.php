<?php
    function is_decimal($val)
    {
        return is_numeric( $val ) && floor( $val ) != $val;
    }

    function curr($num)
    {
        if (is_decimal($num)) {
            $hasil_rupiah = number_format($num,2,',','.');
        } else {
            $hasil_rupiah = number_format($num, 0, ',', '.');
        }

        $hasil_rupiah = number_format($num,2,',','.');

        return $hasil_rupiah;
    }

    function num($num, $dec = 0)
    {
        $hasil = 0;

        if (is_decimal($num)) {
            if ($dec == 0) {
                $dec = 2;
            }
        }

        $hasil = number_format($num, $dec, ',', '.');

        return $hasil;
    }
?>
