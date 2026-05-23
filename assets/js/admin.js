/**
 * BETELITE - admin.js
 * Safety validations on user suspension, live event logs creations
 */

function confirmActions(message, onConfirm) {
    if (confirm(message)) {
         onConfirm();
    }
}
