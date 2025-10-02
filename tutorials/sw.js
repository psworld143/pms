/**
 * Service Worker for Tutorial System
 * Hotel PMS Training System - Interactive Tutorials
 */

const CACHE_NAME = 'pms-tutorials-v1';
const urlsToCache = [
    '/pms/tutorials/mobile.php',
    '/pms/tutorials/index.php',
    '/pms/tutorials/assessment.php',
    '/pms/tutorials/analytics.php',
    '/pms/assets/css/pms-styles.css',
    '/pms/assets/js/pms-scripts.js',
    'https://cdn.tailwindcss.com',
    'https://code.jquery.com/jquery-3.6.0.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/chart.js'
];

// Install event
self.addEventListener('install', function(event) {
    console.log('Service Worker: Install event');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                console.log('Service Worker: Caching files');
                return cache.addAll(urlsToCache);
            })
            .catch(function(error) {
                console.log('Service Worker: Cache failed', error);
            })
    );
    
    // Force the waiting service worker to become the active service worker
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', function(event) {
    console.log('Service Worker: Activate event');
    
    event.waitUntil(
        caches.keys().then(function(cacheNames) {
            return Promise.all(
                cacheNames.map(function(cacheName) {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Deleting old cache', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    
    // Take control of all pages immediately
    return self.clients.claim();
});

// Fetch event
self.addEventListener('fetch', function(event) {
    console.log('Service Worker: Fetch event for', event.request.url);
    
    // Skip cross-origin requests
    if (!event.request.url.startsWith(self.location.origin)) {
        return;
    }
    
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                // Return cached version or fetch from network
                if (response) {
                    console.log('Service Worker: Serving from cache', event.request.url);
                    return response;
                }
                
                console.log('Service Worker: Fetching from network', event.request.url);
                return fetch(event.request).then(function(response) {
                    // Check if we received a valid response
                    if (!response || response.status !== 200 || response.type !== 'basic') {
                        return response;
                    }
                    
                    // Clone the response
                    const responseToCache = response.clone();
                    
                    // Cache the fetched response
                    caches.open(CACHE_NAME)
                        .then(function(cache) {
                            cache.put(event.request, responseToCache);
                        });
                    
                    return response;
                }).catch(function(error) {
                    console.log('Service Worker: Fetch failed', error);
                    
                    // Return offline page for navigation requests
                    if (event.request.mode === 'navigate') {
                        return caches.match('/pms/tutorials/mobile.php');
                    }
                    
                    throw error;
                });
            })
    );
});

// Background sync for offline progress
self.addEventListener('sync', function(event) {
    console.log('Service Worker: Background sync event');
    
    if (event.tag === 'tutorial-progress-sync') {
        event.waitUntil(syncTutorialProgress());
    }
});

// Push notifications
self.addEventListener('push', function(event) {
    console.log('Service Worker: Push event');
    
    const options = {
        body: event.data ? event.data.text() : 'New tutorial content available!',
        icon: '/pms/assets/images/seait-logo.png',
        badge: '/pms/assets/images/badge-icon.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'View Tutorials',
                icon: '/pms/assets/images/tutorial-icon.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/pms/assets/images/close-icon.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('PMS Tutorials', options)
    );
});

// Notification click
self.addEventListener('notificationclick', function(event) {
    console.log('Service Worker: Notification click event');
    
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/pms/tutorials/mobile.php')
        );
    } else if (event.action === 'close') {
        // Just close the notification
        return;
    } else {
        // Default action - open the app
        event.waitUntil(
            clients.openWindow('/pms/tutorials/mobile.php')
        );
    }
});

// Message handling
self.addEventListener('message', function(event) {
    console.log('Service Worker: Message event', event.data);
    
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'CACHE_TUTORIAL_DATA') {
        cacheTutorialData(event.data.data);
    }
});

// Helper functions
function syncTutorialProgress() {
    console.log('Service Worker: Syncing tutorial progress');
    
    // Get stored progress from IndexedDB
    return new Promise(function(resolve) {
        // This would typically interact with IndexedDB
        // For now, just resolve
        resolve();
    });
}

function cacheTutorialData(data) {
    console.log('Service Worker: Caching tutorial data');
    
    return caches.open(CACHE_NAME).then(function(cache) {
        const response = new Response(JSON.stringify(data), {
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        return cache.put('/pms/api/tutorials/cached-data', response);
    });
}

// Periodic background sync (if supported)
self.addEventListener('periodicsync', function(event) {
    console.log('Service Worker: Periodic sync event');
    
    if (event.tag === 'tutorial-content-sync') {
        event.waitUntil(syncTutorialContent());
    }
});

function syncTutorialContent() {
    console.log('Service Worker: Syncing tutorial content');
    
    // Fetch latest tutorial content
    return fetch('/pms/api/tutorials/get-modules.php')
        .then(function(response) {
            if (response.ok) {
                return response.json();
            }
            throw new Error('Failed to fetch tutorial content');
        })
        .then(function(data) {
            // Cache the updated content
            return cacheTutorialData(data);
        })
        .catch(function(error) {
            console.log('Service Worker: Content sync failed', error);
        });
}
