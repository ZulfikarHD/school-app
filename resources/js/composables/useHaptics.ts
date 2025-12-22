/**
 * Haptic feedback composable untuk tactile response iOS-like
 * menggunakan Vibration API dengan pattern yang berbeda
 */

export const useHaptics = () => {
    /**
     * Check apakah browser support Vibration API
     */
    const isSupported = 'vibrate' in navigator;

    /**
     * Light tap feedback untuk button press dan selections
     */
    const light = () => {
        if (isSupported) {
            navigator.vibrate(10);
        }
    };

    /**
     * Medium impact untuk confirmations dan important actions
     */
    const medium = () => {
        if (isSupported) {
            navigator.vibrate(20);
        }
    };

    /**
     * Heavy impact untuk errors dan critical actions
     */
    const heavy = () => {
        if (isSupported) {
            navigator.vibrate(40);
        }
    };

    /**
     * Success pattern untuk completed actions
     */
    const success = () => {
        if (isSupported) {
            navigator.vibrate([10, 50, 10]);
        }
    };

    /**
     * Error pattern untuk failed actions
     */
    const error = () => {
        if (isSupported) {
            navigator.vibrate([30, 50, 30, 50, 30]);
        }
    };

    /**
     * Selection changed feedback
     */
    const selection = () => {
        if (isSupported) {
            navigator.vibrate(5);
        }
    };

    return {
        isSupported,
        light,
        medium,
        heavy,
        success,
        error,
        selection,
    };
};

