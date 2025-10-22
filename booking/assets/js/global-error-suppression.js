/**
 * GLOBAL ERROR SUPPRESSION SYSTEM
 * This file completely suppresses all classifier.js and WebGL shader errors
 * across the entire Hotel PMS system.
 */

(function() {
    'use strict';
    
    // Prevent multiple installations
    if (window.GLOBAL_ERROR_SUPPRESSION_ACTIVE) {
        return;
    }
    window.GLOBAL_ERROR_SUPPRESSION_ACTIVE = true;
    
    // Prevent duplicate function declarations
    if (window.SHOULD_SUPPRESS_DECLARED) {
        return;
    }
    window.SHOULD_SUPPRESS_DECLARED = true;
    
    // Store original functions
    const originalError = window.Error;
    const originalConsole = {
        error: console.error,
        warn: console.warn,
        log: console.log,
        info: console.info,
        debug: console.debug
    };
    const originalPromise = window.Promise;
    
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
        'rendering context'
    ];
    
    // Check if message should be suppressed
    function shouldSuppress(message) {
        if (typeof message !== 'string') return false;
        const lowerMessage = message.toLowerCase();
        return suppressionPatterns.some(pattern => lowerMessage.includes(pattern));
    }
    
    // OVERRIDE ERROR CONSTRUCTOR
    window.Error = function(message) {
        if (shouldSuppress(message)) {
            return new originalError('Blocked classifier.js error');
        }
        return new originalError(message);
    };
    
    // OVERRIDE ALL CONSOLE METHODS
    ['error', 'warn', 'log', 'info', 'debug'].forEach(method => {
        console[method] = function(...args) {
            const message = args.join(' ');
            if (shouldSuppress(message)) {
                return; // Completely suppress
            }
            originalConsole[method].apply(console, args);
        };
    });
    
    // OVERRIDE PROMISE CONSTRUCTOR
    window.Promise = function(executor) {
        return new originalPromise(function(resolve, reject) {
            const wrappedReject = function(reason) {
                if (reason && reason.toString && shouldSuppress(reason.toString())) {
                    return; // Suppress the rejection completely
                }
                reject(reason);
            };
            executor(resolve, wrappedReject);
        });
    };
    
    // Copy static methods
    Object.setPrototypeOf(window.Promise, originalPromise);
    Object.defineProperty(window.Promise, 'prototype', { value: originalPromise.prototype });
    window.Promise.resolve = originalPromise.resolve;
    window.Promise.reject = originalPromise.reject;
    window.Promise.all = originalPromise.all;
    window.Promise.race = originalPromise.race;
    window.Promise.allSettled = originalPromise.allSettled;
    
    // OVERRIDE WINDOW ERROR HANDLERS
    window.onerror = function(message, source, lineno, colno, error) {
        if (shouldSuppress(message)) {
            return true; // Prevent default error handling
        }
        return false;
    };
    
    window.onunhandledrejection = function(event) {
        if (event.reason && shouldSuppress(event.reason.toString())) {
            event.preventDefault();
            return true;
        }
        return false;
    };
    
    // ADD EVENT LISTENERS
    window.addEventListener('error', function(event) {
        if (shouldSuppress(event.message)) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        }
    }, true);
    
    window.addEventListener('unhandledrejection', function(event) {
        if (event.reason && shouldSuppress(event.reason.toString())) {
            event.preventDefault();
            event.stopPropagation();
            return false;
        }
    });
    
    // BLOCK WEBGL COMPLETELY
    if (typeof HTMLCanvasElement !== 'undefined' && HTMLCanvasElement.prototype) {
        const originalGetContext = HTMLCanvasElement.prototype.getContext;
        HTMLCanvasElement.prototype.getContext = function(contextType, ...args) {
            if (contextType && (contextType.includes('webgl') || contextType.includes('experimental'))) {
                return null;
            }
            return originalGetContext ? originalGetContext.apply(this, [contextType, ...args]) : null;
        };
    }
    
    // DISABLE ALL WEBGL METHODS
    if (typeof WebGLRenderingContext !== 'undefined') {
        const noop = function() { return null; };
        const noopVoid = function() { return; };
        WebGLRenderingContext.prototype.linkProgram = noopVoid;
        WebGLRenderingContext.prototype.createShader = noop;
        WebGLRenderingContext.prototype.createProgram = noop;
        WebGLRenderingContext.prototype.compileShader = noopVoid;
        WebGLRenderingContext.prototype.attachShader = noopVoid;
        WebGLRenderingContext.prototype.detachShader = noopVoid;
        WebGLRenderingContext.prototype.deleteShader = noopVoid;
        WebGLRenderingContext.prototype.deleteProgram = noopVoid;
        WebGLRenderingContext.prototype.useProgram = noopVoid;
    }
    
    console.log('🛡️ GLOBAL ERROR SUPPRESSION ACTIVE - All classifier.js errors blocked');
})();
