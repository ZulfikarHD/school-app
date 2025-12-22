import { ref, onMounted, onUnmounted, type Ref } from 'vue';

/**
 * Swipe gesture detection composable untuk iOS-like gestures
 * Support swipe left, right, up, down dengan threshold
 */

interface SwipeOptions {
    threshold?: number;
    onSwipeLeft?: () => void;
    onSwipeRight?: () => void;
    onSwipeUp?: () => void;
    onSwipeDown?: () => void;
}

export const useSwipeGesture = (elementRef: Ref<HTMLElement | null>, options: SwipeOptions = {}) => {
    const {
        threshold = 50,
        onSwipeLeft,
        onSwipeRight,
        onSwipeUp,
        onSwipeDown,
    } = options;

    const touchStartX = ref(0);
    const touchStartY = ref(0);
    const touchEndX = ref(0);
    const touchEndY = ref(0);

    /**
     * Handle touch start untuk capture starting position
     */
    const handleTouchStart = (e: TouchEvent) => {
        touchStartX.value = e.touches[0].clientX;
        touchStartY.value = e.touches[0].clientY;
    };

    /**
     * Handle touch end untuk detect swipe direction
     */
    const handleTouchEnd = (e: TouchEvent) => {
        touchEndX.value = e.changedTouches[0].clientX;
        touchEndY.value = e.changedTouches[0].clientY;
        handleSwipe();
    };

    /**
     * Calculate swipe direction dan trigger callback
     */
    const handleSwipe = () => {
        const diffX = touchStartX.value - touchEndX.value;
        const diffY = touchStartY.value - touchEndY.value;

        // Horizontal swipe
        if (Math.abs(diffX) > Math.abs(diffY)) {
            if (Math.abs(diffX) > threshold) {
                if (diffX > 0) {
                    onSwipeLeft?.();
                } else {
                    onSwipeRight?.();
                }
            }
        }
        // Vertical swipe
        else {
            if (Math.abs(diffY) > threshold) {
                if (diffY > 0) {
                    onSwipeUp?.();
                } else {
                    onSwipeDown?.();
                }
            }
        }
    };

    onMounted(() => {
        if (elementRef.value) {
            elementRef.value.addEventListener('touchstart', handleTouchStart, { passive: true });
            elementRef.value.addEventListener('touchend', handleTouchEnd, { passive: true });
        }
    });

    onUnmounted(() => {
        if (elementRef.value) {
            elementRef.value.removeEventListener('touchstart', handleTouchStart);
            elementRef.value.removeEventListener('touchend', handleTouchEnd);
        }
    });

    return {
        touchStartX,
        touchStartY,
        touchEndX,
        touchEndY,
    };
};

