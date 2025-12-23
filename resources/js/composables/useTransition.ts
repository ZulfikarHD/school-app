/**
 * Composable untuk Vue Transition callbacks
 * dengan proper TypeScript typing dan timing control
 *
 * Digunakan untuk handle transition enter/leave events
 * dengan delay yang konsisten untuk smooth animations
 */

export function useTransition() {
    /**
     * Callback untuk transition enter/leave dengan delay
     * @param duration - Durasi delay dalam milliseconds (default: 300ms)
     */
    const onTransitionEnd = (duration: number = 300) => {
        return (_el: Element, done: () => void) => {
            window.setTimeout(done, duration);
        };
    };

    return {
        onTransitionEnd,
    };
}

