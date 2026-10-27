<?php

return [

    /*
    | Public support address shown on the v2 Contact / Careers pages.
    | Set CONTACT_EMAIL in .env; falls back to the mail "from" address.
    */
    'email' => env('CONTACT_EMAIL', env('MAIL_FROM_ADDRESS')),

];
