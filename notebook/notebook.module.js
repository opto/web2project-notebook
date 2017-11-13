/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function require(script) {
    $.ajax({
        url: script,
        dataType: "script",
        async: false,           // <-- This is the key
        success: function () {
            // all good...
           // alert(script);
        },
        error: function () {
            alert("Could not load script " + script);
            throw new Error("Could not load script " + script);
        }
    });
}

var CKEDITOR_BASEPATH=  "./modules/notebook/js/ckeditor/";
require("./modules/notebook/js/ckeditor/ckeditor.js");
