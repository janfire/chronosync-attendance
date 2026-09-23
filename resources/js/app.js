import './bootstrap';
import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

document.addEventListener('DOMContentLoaded', function() {
    // Initialize for datetime-local
    flatpickr('input[type="datetime-local"]', {
        enableTime: true,
        dateFormat: "Y-m-d\\TH:i",
        altInput: true,
        altFormat: "F j, Y h:i K"
    });

    // Initialize for standard date
    flatpickr('input[type="date"]', {
        enableTime: false,
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y"
    });
});
