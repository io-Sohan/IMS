/**
 * Common helpers - error toast, etc. Use across the project.
 */

(function () {
    "use strict";

    var TOAST_CONTAINER_ID = "commonToastContainer";
    var TOAST_AUTO_HIDE_MS = 5000;

    function getOrCreateToastContainer() {
        var el = document.getElementById(TOAST_CONTAINER_ID);
        if (el) return el;
        el = document.createElement("div");
        el.id = TOAST_CONTAINER_ID;
        el.className = "toast-container position-fixed top-0 end-0 p-3";
        el.setAttribute("aria-live", "polite");
        el.setAttribute("aria-atomic", "true");
        document.body.appendChild(el);
        return el;
    }

    /**
     * Get a user-friendly message from an axios/API error. Use with showErrorToast(getErrorMessage(err)).
     * @param {*} err - Error object (e.g. from catch)
     * @param {string} fallback - Default message if none found
     * @returns {string}
     */
    window.getErrorMessage = function (err, fallback) {
        fallback = fallback || "Something went wrong. Please try again.";
        var data = err.response && err.response.data;
        if (data && data.errors) {
            var first = Object.values(data.errors)[0];
            return Array.isArray(first) ? first[0] : first;
        }
        if (data && data.message) return data.message;
        if (err.message) return err.message;
        return fallback;
    };

    /**
     * Show an error message as a toast. Call from anywhere: showErrorToast('Something went wrong');
     * @param {string} message - Error message to show
     */
    window.showErrorToast = function (message) {
        if (!message) message = "An error occurred.";
        var container = getOrCreateToastContainer();
        var toastId = "toast-" + Date.now();
        var toastEl = document.createElement("div");
        toastEl.id = toastId;
        toastEl.className = "toast align-items-center text-bg-danger border-0";
        toastEl.setAttribute("role", "alert");
        toastEl.innerHTML =
            '<div class="d-flex">' +
            '<div class="toast-body">' +
            escapeHtml(message) +
            "</div>" +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            "</div>";
        container.appendChild(toastEl);
        var toast = new window.bootstrap.Toast(toastEl, {
            delay: TOAST_AUTO_HIDE_MS,
        });
        toastEl.addEventListener("hidden.bs.toast", function () {
            toastEl.remove();
        });
        toast.show();
    };

    /**
     * Show a success message as a toast. Call from anywhere: showSuccessToast('Saved.');
     * @param {string} message - Success message to show
     */
    window.showSuccessToast = function (message) {
        if (!message) message = "Success.";
        var container = getOrCreateToastContainer();
        var toastId = "toast-" + Date.now();
        var toastEl = document.createElement("div");
        toastEl.id = toastId;
        toastEl.className = "toast align-items-center text-bg-success border-0";
        toastEl.setAttribute("role", "alert");
        toastEl.innerHTML =
            '<div class="d-flex">' +
            '<div class="toast-body">' +
            escapeHtml(message) +
            "</div>" +
            '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
            "</div>";
        container.appendChild(toastEl);
        var toast = new window.bootstrap.Toast(toastEl, {
            delay: TOAST_AUTO_HIDE_MS,
        });
        toastEl.addEventListener("hidden.bs.toast", function () {
            toastEl.remove();
        });
        toast.show();
    };

    function escapeHtml(text) {
        var div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
})();
