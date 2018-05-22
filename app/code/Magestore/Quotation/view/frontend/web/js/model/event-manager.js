/*
 *  Copyright © 2016 Magestore. All rights reserved.
 *  See COPYING.txt for license details.
 *
 */

define(
    [
        'jquery'
    ],
    function ($) {
        "use strict";

        return {
            dispatch: function (eventName, data, timeout) {
                $("body").eventName = '';
                if (timeout) {
                    setTimeout(function () {
                        $("body").trigger(eventName, data);
                    }, 100);
                } else $("body").trigger(eventName, data);
                return true;
            },
            observer: function (eventName, function_callback) {
                $("body").on(eventName, function_callback);
                return true;
            }
        };
    }
);