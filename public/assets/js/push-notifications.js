/**
 * UEDF SENTINEL - Push Notifications
 */

// Request permission
function requestNotificationPermission() {
  if (!('Notification' in window)) {
    console.log('This browser does not support notifications');
    return;
  }
  
  Notification.requestPermission().then(permission => {
    if (permission === 'granted') {
      console.log('Notification permission granted');
      subscribeUser();
    }
  });
}

// Subscribe to push notifications
function subscribeUser() {
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.ready.then(registration => {
      registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array('YOUR_PUBLIC_VAPID_KEY')
      }).then(subscription => {
        console.log('User is subscribed:', subscription);
        // Send subscription to server
        fetch('/sentinel/api/subscribe.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(subscription)
        });
      }).catch(err => {
        console.log('Subscription failed:', err);
      });
    });
  }
}

// Utility function
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}