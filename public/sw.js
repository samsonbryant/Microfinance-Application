const CACHE_NAME = 'mms-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Files to cache for offline functionality
const STATIC_CACHE_URLS = [
    '/',
    '/dashboard',
    '/offline.html',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json',
    // Add other critical assets
];

// API endpoints to cache
const API_CACHE_URLS = [
    '/api/dashboard/metrics',
    '/api/loans',
    '/api/clients',
    '/api/transactions',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('Service Worker installing...');
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('Caching static assets...');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => {
                console.log('Static assets cached successfully');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('Failed to cache static assets:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('Service Worker activating...');
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve cached content when offline
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Handle API requests
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache successful API responses
                    if (response.status === 200) {
                        const responseClone = response.clone();
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(request, responseClone);
                            });
                    }
                    return response;
                })
                .catch(() => {
                    // Return cached API response if available
                    return caches.match(request)
                        .then((cachedResponse) => {
                            if (cachedResponse) {
                                return cachedResponse;
                            }
                            // Return offline response for API calls
                            return new Response(
                                JSON.stringify({ 
                                    error: 'Offline', 
                                    message: 'No internet connection available' 
                                }),
                                {
                                    status: 503,
                                    statusText: 'Service Unavailable',
                                    headers: { 'Content-Type': 'application/json' }
                                }
                            );
                        });
                })
        );
        return;
    }
    
    // Handle page requests
    event.respondWith(
        fetch(request)
            .then((response) => {
                // Cache successful page responses
                if (response.status === 200) {
                    const responseClone = response.clone();
                    caches.open(CACHE_NAME)
                        .then((cache) => {
                            cache.put(request, responseClone);
                        });
                }
                return response;
            })
            .catch(() => {
                // Return cached page or offline page
                return caches.match(request)
                    .then((cachedResponse) => {
                        if (cachedResponse) {
                            return cachedResponse;
                        }
                        // Return offline page for navigation requests
                        if (request.headers.get('accept').includes('text/html')) {
                            return caches.match(OFFLINE_URL);
                        }
                        // Return a basic offline response for other requests
                        return new Response('Offline', {
                            status: 503,
                            statusText: 'Service Unavailable'
                        });
                    });
            })
    );
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('Background sync triggered:', event.tag);
    
    if (event.tag === 'loan-application') {
        event.waitUntil(syncLoanApplications());
    } else if (event.tag === 'payment') {
        event.waitUntil(syncPayments());
    }
});

// Sync loan applications when back online
async function syncLoanApplications() {
    try {
        const pendingApplications = await getPendingLoanApplications();
        
        for (const application of pendingApplications) {
            try {
                const response = await fetch('/api/loans', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken()
                    },
                    body: JSON.stringify(application)
                });
                
                if (response.ok) {
                    await removePendingLoanApplication(application.id);
                    console.log('Synced loan application:', application.id);
                }
            } catch (error) {
                console.error('Failed to sync loan application:', error);
            }
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

// Sync payments when back online
async function syncPayments() {
    try {
        const pendingPayments = await getPendingPayments();
        
        for (const payment of pendingPayments) {
            try {
                const response = await fetch('/api/payments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': getCSRFToken()
                    },
                    body: JSON.stringify(payment)
                });
                
                if (response.ok) {
                    await removePendingPayment(payment.id);
                    console.log('Synced payment:', payment.id);
                }
            } catch (error) {
                console.error('Failed to sync payment:', error);
            }
        }
    } catch (error) {
        console.error('Background sync failed:', error);
    }
}

// Helper functions for offline storage
function getPendingLoanApplications() {
    return new Promise((resolve) => {
        const request = indexedDB.open('MMS_Offline', 1);
        request.onsuccess = (event) => {
            const db = event.target.result;
            const transaction = db.transaction(['loanApplications'], 'readonly');
            const store = transaction.objectStore('loanApplications');
            const getAllRequest = store.getAll();
            
            getAllRequest.onsuccess = () => {
                resolve(getAllRequest.result);
            };
        };
    });
}

function getPendingPayments() {
    return new Promise((resolve) => {
        const request = indexedDB.open('MMS_Offline', 1);
        request.onsuccess = (event) => {
            const db = event.target.result;
            const transaction = db.transaction(['payments'], 'readonly');
            const store = transaction.objectStore('payments');
            const getAllRequest = store.getAll();
            
            getAllRequest.onsuccess = () => {
                resolve(getAllRequest.result);
            };
        };
    });
}

function removePendingLoanApplication(id) {
    return new Promise((resolve) => {
        const request = indexedDB.open('MMS_Offline', 1);
        request.onsuccess = (event) => {
            const db = event.target.result;
            const transaction = db.transaction(['loanApplications'], 'readwrite');
            const store = transaction.objectStore('loanApplications');
            store.delete(id);
            resolve();
        };
    });
}

function removePendingPayment(id) {
    return new Promise((resolve) => {
        const request = indexedDB.open('MMS_Offline', 1);
        request.onsuccess = (event) => {
            const db = event.target.result;
            const transaction = db.transaction(['payments'], 'readwrite');
            const store = transaction.objectStore('payments');
            store.delete(id);
            resolve();
        };
    });
}

function getCSRFToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

// Push notification handling
self.addEventListener('push', (event) => {
    console.log('Push notification received:', event);
    
    const options = {
        body: event.data ? event.data.text() : 'New notification from MMS',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/badge-72x72.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'View Details',
                icon: '/icons/checkmark.png'
            },
            {
                action: 'close',
                title: 'Close',
                icon: '/icons/xmark.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification('MMS Notification', options)
    );
});

// Notification click handling
self.addEventListener('notificationclick', (event) => {
    console.log('Notification clicked:', event);
    
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/dashboard')
        );
    }
});
