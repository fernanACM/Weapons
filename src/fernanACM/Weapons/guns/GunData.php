<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM

namespace fernanACM\Weapons\guns;

interface GunData{

    public const GUN_LIST = [
            "mg42",
            "mp40",
            "minigun",
            "thompson",
            "m1911",
            "panzerfaust"
        ];

     public const FIRE_RATES = [
            "mg42" => 1,
            "mp40" => 3,
            "minigun" => 0,
            "thompson" => 2,
        ];

    public const SHOT_PITCH = [
            "mg42" => 0.4,
            "mp40" => 0.7,
            "minigun" => 0.6,
            "thompson" => 0.5,
            "m1911" => 0.5,
            "panzerfaust" => 0.1
        ];

    public const DAMAGES = [
            "mg42" => 1,
            "mp40" => 1,
            "minigun" => 4,
            "thompson" => 1,
            "m1911" => 1,
            "panzerfaust" => 1,
        ];

    public const FULL_AUTO = [
            "mg42",
            "mp40",
            "minigun",
            "thompson"
        ];


    public const EXPLODE = [
            "panzerfaust" => 5
        ];
}