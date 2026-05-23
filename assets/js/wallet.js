/**
 * BETELITE - wallet.js
 * Payout thresholds, crypto copy checks, reference logging keys
 */

function copyCryptoAddress(address) {
    navigator.clipboard.writeText(address).then(() => {
        alert("Crypto wallet address copied successfully to clipboard!");
    });
}
