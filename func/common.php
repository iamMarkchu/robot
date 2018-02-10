<?php
function my_base64_decode($str)
{
    $str = str_replace("@@", '+', $str);
    $str = str_replace("~~", '/', $str);
    $str = base64_decode($str);
    return $str;
}


function cwm_get_source_link($go_url)
{
    $tmpLinkBlock = explode('|', urldecode(str_replace('/go/', '', $go_url)));
    if(count($tmpLinkBlock) > 0)
    {
        $tmpLinkBlock = explode('|', urldecode($tmpLinkBlock[0]));
        return urldecode(base64_decode($tmpLinkBlock[3]));
    }else{
        return '';
    }
}