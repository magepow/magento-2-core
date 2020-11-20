<?php

/**
 * @Author: Alex Dong
 * @Date:   2020-07-14 19:36:33
 * @Last Modified by:   Alex Dong
 * @Last Modified time: 2020-11-20 11:22:22
 */

namespace Magepow\Core\Model\Config\Source;

class Percent
{
    public static function getPercent()
    {
        return array(
            1   => '100%', 
            2   => '50%', 
            3   => '33.333333333%', 
            4   => '25%', 
            5   => '20%', 
            6   => '16.666666666%', 
            7   => '14.285714285%', 
            8   => '12.5%', 
            9   => '11.111111111%',
            10  => '10%',
            11  => '9.090909090%',
            12  => '8.333333333%'
        );
    }

    public static function getNumberPercent()
    {
        return array(
            1   => '100', 
            2   => '50', 
            3   => '33.333333333', 
            4   => '25', 
            5   => '20', 
            6   => '16.666666666', 
            7   => '14.285714285', 
            8   => '12.5', 
            9   => '11.111111111',
            10  => '10',
            11  => '9.090909090',
            12  => '8.333333333'
        );
    }

}
