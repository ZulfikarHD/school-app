import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Composable untuk mendeteksi session timeout dengan idle detection
 * Menampilkan warning modal 2 menit sebelum timeout dan auto-logout
 */

interface SessionTimeoutOptions {
    /** Timeout duration dalam menit (default: 30 untuk user biasa, 15 untuk admin) */
    timeoutMinutes?: number;
    /** Warning sebelum timeout dalam menit (default: 2) */
    warningMinutes?: number;
    /** Callback saat warning ditampilkan */
    onWarning?: () => void;
    /** Callback saat timeout terjadi */
    onTimeout?: () => void;
}

export const useSessionTimeout = (options: SessionTimeoutOptions = {}) => {
    const {
        timeoutMinutes = 30,
        warningMinutes = 2,
        onWarning,
        onTimeout,
    } = options;

    const showWarning = ref(false);
    const remainingSeconds = ref(0);
    let lastActivity = Date.now();
    let checkInterval: ReturnType<typeof setInterval> | null = null;
    let countdownInterval: ReturnType<typeof setInterval> | null = null;

    const timeoutMs = timeoutMinutes * 60 * 1000;
    const warningMs = warningMinutes * 60 * 1000;

    /**
     * Update last activity timestamp
     */
    const updateActivity = () => {
        lastActivity = Date.now();
        if (showWarning.value) {
            showWarning.value = false;
            stopCountdown();
        }
    };

    /**
     * Check jika user sedang idle
     */
    const checkIdleStatus = () => {
        const now = Date.now();
        const idleTime = now - lastActivity;
        const timeUntilTimeout = timeoutMs - idleTime;

        // Show warning jika mendekati timeout
        if (timeUntilTimeout <= warningMs && timeUntilTimeout > 0 && !showWarning.value) {
            showWarning.value = true;
            remainingSeconds.value = Math.floor(timeUntilTimeout / 1000);
            startCountdown();
            onWarning?.();
        }

        // Auto logout jika timeout
        if (idleTime >= timeoutMs) {
            handleTimeout();
        }
    };

    /**
     * Start countdown timer
     */
    const startCountdown = () => {
        if (countdownInterval) clearInterval(countdownInterval);

        countdownInterval = setInterval(() => {
            remainingSeconds.value--;
            if (remainingSeconds.value <= 0) {
                handleTimeout();
            }
        }, 1000);
    };

    /**
     * Stop countdown timer
     */
    const stopCountdown = () => {
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    };

    /**
     * Handle session timeout
     */
    const handleTimeout = () => {
        stopCountdown();
        if (checkInterval) clearInterval(checkInterval);
        showWarning.value = false;
        onTimeout?.();

        // Logout user
        router.post('/logout', {}, {
            onSuccess: () => {
                // Show session expired message
                // This will be handled by the login page or a redirect
            },
        });
    };

    /**
     * Extend session (user clicked "Perpanjang Session")
     */
    const extendSession = () => {
        updateActivity();
    };

    /**
     * Manual logout
     */
    const logout = () => {
        stopCountdown();
        if (checkInterval) clearInterval(checkInterval);
        router.post('/logout');
    };

    /**
     * Start monitoring
     */
    const start = () => {
        // Update activity on user interactions
        const events = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
        events.forEach(event => {
            window.addEventListener(event, updateActivity, { passive: true });
        });

        // Check idle status every 10 seconds
        checkInterval = setInterval(checkIdleStatus, 10000);

        // Also handle Inertia 419 errors (session expired)
        router.on('error', (event) => {
            // @ts-expect-error - event.detail may not have response property
            if (event.detail?.response?.status === 419) {
                handleTimeout();
            }
        });
    };

    /**
     * Stop monitoring
     */
    const stop = () => {
        const events = ['mousedown', 'keydown', 'scroll', 'touchstart', 'click'];
        events.forEach(event => {
            window.removeEventListener(event, updateActivity);
        });

        if (checkInterval) {
            clearInterval(checkInterval);
            checkInterval = null;
        }

        stopCountdown();
    };

    // Auto start on mount
    onMounted(() => {
        start();
    });

    // Auto cleanup on unmount
    onUnmounted(() => {
        stop();
    });

    return {
        showWarning,
        remainingSeconds,
        extendSession,
        logout,
        start,
        stop,
    };
};
