
document.addEventListener('DOMContentLoaded', function () {
    const notificationDropdown = document.getElementById('notificationDropdown');
    if (notificationDropdown) {
        fetchNotifications();
        notificationDropdown.addEventListener('click', function () {
            fetchNotifications();
        });
    }

    document.addEventListener('click', function (event) {
        if (event.target.matches('.mark-as-read')) {
            const notificationId = event.target.dataset.id;
            markAsRead(notificationId);
        }
        if (event.target.matches('#mark-all-as-read')) {
            markAllAsRead();
        }
    });
});

async function fetchNotifications() {
    try {
        const response = await fetch('/api/notifications', {
            headers: {
                'Authorization': `Bearer ${getApiToken()}`,
                'Accept': 'application/json',
            }
        });
        const data = await response.json();
        renderNotifications(data);
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
}

function renderNotifications(data) {
    const unreadCount = data.unread.length;
    const unreadBadge = document.getElementById('unread-count');
    const notificationList = document.getElementById('notification-list');

    if (unreadBadge) {
        unreadBadge.textContent = unreadCount;
        if (unreadCount > 0) {
            unreadBadge.classList.remove('d-none');
        } else {
            unreadBadge.classList.add('d-none');
        }
    }

    let notificationsHtml = '';
    if (data.unread.length === 0 && data.read.length === 0) {
        notificationsHtml = '<li class="list-group-item">No notifications</li>';
    } else {
        data.unread.forEach(notification => {
            notificationsHtml += `
                <li class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1">${notification.data.message}</p>
                        <small class="text-muted">${new Date(notification.created_at).toLocaleDateString()}</small>
                    </div>
                    <button class="btn btn-sm btn-link p-0 mark-as-read" data-id="${notification.id}">Mark as read</button>
                </li>
            `;
        });
        data.read.forEach(notification => {
            notificationsHtml += `
                <li class="list-group-item list-group-item-light">
                    <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1">${notification.data.message}</p>
                        <small class="text-muted">${new Date(notification.created_at).toLocaleDateString()}</small>
                    </div>
                </li>
            `;
        });
    }
    notificationList.innerHTML = notificationsHtml;
}

async function markAsRead(notificationId) {
    try {
        await fetch(`/api/notifications/${notificationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getApiToken()}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        fetchNotifications();
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
}

async function markAllAsRead() {
    try {
        await fetch('/api/notifications/mark-all-as-read', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getApiToken()}`,
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        fetchNotifications();
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
}

function getApiToken() {
    // This function needs to be implemented based on how you store the API token.
    // For example, if it's in localStorage:
    return localStorage.getItem('auth_token');
}
