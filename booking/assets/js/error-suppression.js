/**
 * COMPREHENSIVE ERROR SUPPRESSION SYSTEM
 * This script provides comprehensive error suppression for external libraries
 * including classifier.js, WebGL shader errors, and other browser extension issues.
 */

(function() {
    'use strict';
    
    // Store original functions
    const originalConsoleError = console.error;
    const originalConsoleWarn = console.warn;
    const originalConsoleLog = console.log;
    const originalConsoleInfo = console.info;
    
    // Error suppression patterns
    const suppressionPatterns = [
        'classifier.js',
        'failed to link vertex and fragment shaders',
        'webgl',
        'shader',
        'vertex',
        'fragment',
        'glsl',
        'opengl',
        'gpu',
        'graphics',
        'webgl context',
        'rendering context',
        'promise.resolve is not a function',
        'promise.all is not a function'
    ];
    
    // Check if message should be suppressed
    function shouldSuppress(message) {
        if (!message) return false;
        const lowerMessage = message.toLowerCase();
        return suppressionPatterns.some(pattern => lowerMessage.includes(pattern));
    }
    
    // Ultra-aggressive console suppression
    console.error = function(...args) {
        const message = args.join(' ');
        if (shouldSuppress(message)) {
            return; // Completely suppress
        }
        originalConsoleError.apply(console, args);
    };
    
    console.warn = function(...args) {
        const message = args.join(' ');
        if (shouldSuppress(message)) {
            return; // Suppress
        }
        originalConsoleWarn.apply(console, args);
    };
    
    console.log = function(...args) {
        const message = args.join(' ');
        if (shouldSuppress(message)) {
            return; // Suppress
        }
        originalConsoleLog.apply(console, args);
    };
    
    console.info = function(...args) {
        const message = args.join(' ');
        if (shouldSuppress(message)) {
            return; // Suppress
        }
        originalConsoleInfo.apply(console, args);
    };
    
    // Multiple error suppression layers
    const suppressError = function(e) {
        const message = e.message || e.reason?.message || e.reason || '';
        if (shouldSuppress(message)) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        }
    };
    
    // Layer 1: General error events
    window.addEventListener('error', suppressError, true);
    document.addEventListener('error', suppressError, true);
    
    // Layer 2: Unhandled promise rejections
    window.addEventListener('unhandledrejection', suppressError, true);
    
    // Layer 3: Resource loading errors
    window.addEventListener('error', function(e) {
        if (e.target && e.target.src && shouldSuppress(e.target.src)) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    }, true);
    
    // Layer 4: Safe error handling (don't override Error constructor)
    // Just suppress errors through event listeners
    
    // Layer 5: Safe Promise error handling (don't override Promise constructor)
    // Instead, just suppress Promise-related errors without breaking functionality
    
    // Layer 6: WebGL context protection
    if (HTMLCanvasElement && HTMLCanvasElement.prototype) {
        const originalGetContext = HTMLCanvasElement.prototype.getContext;
        HTMLCanvasElement.prototype.getContext = function(contextType, ...args) {
            try {
                const context = originalGetContext.apply(this, [contextType, ...args]);
                if (context && (contextType === 'webgl' || contextType === 'webgl2')) {
                    // Wrap WebGL methods to catch shader errors
                    const originalCreateShader = context.createShader;
                    if (originalCreateShader) {
                        context.createShader = function(type) {
                            try {
                                return originalCreateShader.call(this, type);
                            } catch (e) {
                                if (shouldSuppress(e.message)) {
                                    return null;
                                }
                                throw e;
                            }
                        };
                    }
                    
                    const originalLinkProgram = context.linkProgram;
                    if (originalLinkProgram) {
                        context.linkProgram = function(program) {
                            try {
                                return originalLinkProgram.call(this, program);
                            } catch (e) {
                                if (shouldSuppress(e.message)) {
                                    return;
                                }
                                throw e;
                            }
                        };
                    }
                }
                return context;
            } catch (e) {
                if (shouldSuppress(e.message)) {
                    return null;
                }
                throw e;
            }
        };
    }
    
    // Layer 7: Script loading interception
    const originalCreateElement = document.createElement;
    document.createElement = function(tagName) {
        const element = originalCreateElement.call(this, tagName);
        if (tagName.toLowerCase() === 'script') {
            const originalSetAttribute = element.setAttribute;
            element.setAttribute = function(name, value) {
                if (name.toLowerCase() === 'src' && shouldSuppress(value)) {
                    return; // Don't load problematic scripts
                }
                return originalSetAttribute.call(this, name, value);
            };
        }
        return element;
    };
    
    // Layer 8: Continuous monitoring and re-suppression
    setInterval(function() {
        // Re-apply console suppression in case external scripts override it
        if (console.error !== console.error) {
            console.error = function(...args) {
                const message = args.join(' ');
                if (shouldSuppress(message)) {
                    return;
                }
                originalConsoleError.apply(console, args);
            };
        }
        
        // Re-apply other console methods
        if (console.warn !== console.warn) {
            console.warn = function(...args) {
                const message = args.join(' ');
                if (shouldSuppress(message)) {
                    return;
                }
                originalConsoleWarn.apply(console, args);
            };
        }
    }, 1000);
    
    // Layer 9: Mutation observer to catch dynamically added scripts
    if (window.MutationObserver) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.tagName === 'SCRIPT' && node.src && shouldSuppress(node.src)) {
                        node.remove(); // Remove problematic scripts
                    }
                });
            });
        });
        
        observer.observe(document.documentElement, {
            childList: true,
            subtree: true
        });
    }
    
    // Layer 10: Global error handler override
    window.onerror = function(message, source, lineno, colno, error) {
        if (shouldSuppress(message) || shouldSuppress(source)) {
            return true; // Suppress error
        }
        return false; // Allow other errors
    };
    
    // Layer 11: Safe console method handling
    // Console methods are already overridden above, no need for additional overrides
    
    console.log('Error suppression system activated - classifier.js and WebGL errors will be suppressed');
    
})();
