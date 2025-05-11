<?php
    use App\Models\Config;
    use App\Models\Master\Country;
    use Carbon\Carbon;
    function site_config(){
        $config = Config::first();
        return $config;
    }

    function bank_lists(){
        return [
            'BCA',
            'BNI',
            'Mandiri'
        ];
    }

    function comma_separated($number){
        return number_format($number, 0, '.', ',');
    }

    function datetime_stamp($string){
        $datetime = Carbon::parse($string);
        return $datetime->format('Y-m-d')."T".$datetime->format('H:i');
    }

    function format_time($time, $format){
        return Carbon::parse($time)->format($format);
    }

    function country_lists(){
        $country = Country::get();
        return $country;
    }

    function tahun_lists($sort=null){
        $start = 1970;
        $end = date('Y');
        $arr_return = [];
        for($i = $start; $i <= $end ; $i++){
            array_push($arr_return, $i);
        }

        if(!isset($sort)){
            //default in desc
            rsort($arr_return);
        }else{
            sort($arr_return);
        }

        return $arr_return;
    }

    function getAllowedBase64Extension($base64String) {
        if (preg_match('/^data:(.*?);base64,/', $base64String, $matches)) {
            $mimeType = $matches[1];
    
            // Map MIME types to extensions
            $allowedMimeTypes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'application/pdf' => 'pdf',
            ];
    
            if (isset($allowedMimeTypes[$mimeType])) {
                return $allowedMimeTypes[$mimeType];
            }
        }
    
        return null; // Not allowed or not a valid base64
    }