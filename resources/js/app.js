import $ from 'jquery';
import 'bootstrap';
import 'bootstrap-datepicker';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-datepicker/dist/css/bootstrap-datepicker.css';

window.$ = $;
window.jQuery = $;

// Prüfen, ob jQuery und Datepicker geladen sind
//console.log("jQuery Version:", $.fn.jquery);
//console.log("Datepicker:", $.fn.datepicker);


$.fn.datepicker.dates['de'] = {
    days: ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"],
    daysShort: ["Son", "Mon", "Die", "Mit", "Don", "Fre", "Sam"],
    daysMin: ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
    months: ["Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
    monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
    today: "Heute",
    clear: "Rücksetzen",
    format: "DD dd.mm.yyyy",
    titleFormat: "MM yyyy", /* Leverages same syntax as 'format' */
    weekStart: 1
};
// Datepicker initialisieren
$(document).ready(function() {
    console.log("Document ready, initializing datepicker...");
    $('#datepicker').datepicker({
        language: 'de',
        maxViewMode: 1,
        daysOfWeekHighlighted: "0",
        autoclose: true,
        todayHighlight: true
    });
});
