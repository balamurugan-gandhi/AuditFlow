<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Public RSA Key
    |--------------------------------------------------------------------------
    |
    | This key is used to verify the digital signature of your license file.
    | It should NOT be shared with anyone who isn't supposed to verify
    | the license. The private key should NEVER be present in this app.
    |
    */
    'public_key' => "-----BEGIN PUBLIC KEY-----\n" .
        "MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA0Bzb0wlJnrZL+Nl4bQT/\n" .
        "go9uzZY+4qWiBQOfKZBy5yqHTR6zD7/uh2ITsU8YscHa/UzCz8Ntl/tz3p2JG00r\n" .
        "Q6jgtU1DShiSZKBbxarK3I/uWU4p0AZ4Zc2EPxTgYp0cf25z0QgrEifzG25nHOiA\n" .
        "Xb9SZNQuZOQacmOmUJJ/pQVhsPO47MiKo6tBI/uYnCzxP2zSX4AqJbPRN38VO8Te\n" .
        "pA6ASXCyYRiRn0XBvkXeALn/u6x9ABKdy4byWG5/o/9LC9PMCAHaiiIg95DpWf2Z\n" .
        "IQ5s7WQAj+Uk5m0KJjcsx2clsJTYsGPeERID7CAGG3c3HyrUiVGAhAZTSfKj4M2u\n" .
        "kz7UkkyHQJWQxPSzqH7vMHNbmt1VZqRaXY2x1lh+NZ7b+iL3pYBVE6FGltYa0BNI\n" .
        "dv9YtBsNGxmeIRRLEA3w53nSMXT4YG5XqIafNyj/50kxYD0qMbSVSB7nm6QCkoi/\n" .
        "Ab3XaL3xoimBb0JmALjGyF9RTc/S8ZbiGNTwyFfkR+zSnGp0NCw3po0slZXhtDUz\n" .
        "dNytjGEGgEL70gM9qSPTQhUe5QlXrm/Um1cmY7qH2DzhQV2+thwAT7Ij1hOTsOHS\n" .
        "9s20SD8sS4cA/vRTWNhy3a6FsgJmqoHcqAxscSohffEEkjyStaHrCeVBLvA8pixa\n" .
        "ihOPIYtBuTo9Z3V3Ky4LL/UCAwEAAQ==\n" .
        "-----END PUBLIC KEY-----",

    /*
    |--------------------------------------------------------------------------
    | License Path
    |--------------------------------------------------------------------------
    */
    'license_path' => storage_path('app/license/license.lic'),
];
