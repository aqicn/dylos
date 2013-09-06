<?php

class Http
{
    static function post( $url, $postargs )
    {
        $headers = array(
            "Content-Type: application/x-www-form-urlencoded",
                );

        $o = array(
            'http'=> array(
                'method'=>"POST",
                'header'=>implode($headers,"\r\n"),
                'content'=>http_build_query(array("data"=>$postargs)),
                )
            );

        $x = stream_context_create($o);
        $file = file_get_contents($url,false,$x);

        return $file;
    }
}

?>
