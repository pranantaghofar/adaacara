<?php

use App\Libraries\SEO;

if (! function_exists('seo')) {
    function seo(): SEO
    {
        return new SEO();
    }
}
