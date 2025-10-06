document.addEventListener('DOMContentLoaded', function () {
    updateUnreadCountBadge();

    const notificationDropdown = document.getElementById('notificationDropdown');
    if (notificationDropdown) {
        let isFetching = false;
        let hasFetched = false;

        notificationDropdown.addEventListener('show.bs.dropdown', async function () {
            if (!hasFetched && !isFetching) {
                isFetching = true;
                await fetchNotifications();
                isFetching = false;
                hasFetched = true;
            }
        });

        notificationDropdown.addEventListener('hide.bs.dropdown', function() {
            hasFetched = false;
        });

        // Prevent the dropdown from closing when clicking inside
        const dropdownMenu = notificationDropdown.nextElementSibling;
        if (dropdownMenu) {
            dropdownMenu.addEventListener('click', function (event) {
                // Allow clicks on links to perform their default action (navigate)
                if (event.target.closest('a[href]') || event.target.closest('button')) {
                    // Let default button/link behavior happen, but stop dropdown closure
                    event.stopPropagation();
                }
            });
        }
    }

    document.addEventListener('click', function (event) {
        if (event.target.matches('.mark-as-read')) {
            event.preventDefault();
            const notificationId = event.target.dataset.id;
            markAsRead(notificationId);
        }
        if (event.target.matches('#mark-all-as-read')) {
            event.preventDefault();
            markAllAsRead();
        }
    });
});

async function updateUnreadCountBadge() {
    try {
        const response = await fetch('/api/notifications', {
            headers: {
                'Authorization': `Bearer ${getApiToken()}`,
                'Accept': 'application/json',
            }
        });
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        const unreadCount = data.unread.length;
        const unreadBadge = document.getElementById('unread-count');
        if (unreadBadge) {
            unreadBadge.textContent = unreadCount;
            unreadBadge.classList.toggle('d-none', unreadCount === 0);
        }
    } catch (error) {
        console.error('Error fetching unread notification count:', error);
    }
}

async function fetchNotifications() {
    try {
        const response = await fetch('/api/notifications', {
            headers: {
                'Authorization': `Bearer ${getApiToken()}`,
                'Accept': 'application/json',
            }
        });
        if (!response.ok) throw new Error('Network response was not ok');
        
        const data = await response.json();
        renderNotifications(data);
    } catch (error) {
        console.error('Error fetching notifications:', error);
        const listElements = ['service-orders-notification-list', 'invoices-notification-list'];
        listElements.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = '<div class="text-center text-danger p-3">Could not load notifications.</div>';
        });
    }
}

function renderNotifications(data) {
    const unreadCount = data.unread.length;
    const unreadBadge = document.getElementById('unread-count');
    if (unreadBadge) {
        unreadBadge.textContent = unreadCount;
        unreadBadge.classList.toggle('d-none', unreadCount === 0);
    }

    const unreadService = data.unread.filter(n => n.data.message.toLowerCase().includes('service'));
    const readService = data.read.filter(n => n.data.message.toLowerCase().includes('service'));
    
    const unreadInvoice = data.unread.filter(n => n.data.message.toLowerCase().includes('invoice'));
    const readInvoice = data.read.filter(n => n.data.message.toLowerCase().includes('invoice'));

    renderTypedNotificationList(document.getElementById('service-orders-notification-list'), unreadService, readService, 'No service order notifications.');
    renderTypedNotificationList(document.getElementById('invoices-notification-list'), unreadInvoice, readInvoice, 'No invoice notifications.');
}

function renderTypedNotificationList(element, unread, read, emptyMessage) {
    if (!element) return;

    const sortedUnread = unread.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    const sortedRead = read.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    
    const allNotifications = [...sortedUnread, ...sortedRead];
    const latestNotifications = allNotifications.slice(0, 10);

    if (latestNotifications.length === 0) {
        element.innerHTML = `<div class="text-center text-muted p-3">${emptyMessage}</div>`;
        return;
    }

    let notificationsHtml = '';
    latestNotifications.forEach(notification => {
        const isUnread = sortedUnread.some(n => n.id === notification.id);
        const url = notification.data.url || '#';

        if (isUnread) {
            notificationsHtml += `
                <div class="list-group-item list-group-item-action">
                    <a href="${url}" class="text-decoration-none text-body d-block">
                        <div class="d-flex w-100 justify-content-between">
                            <p class="mb-1 small">${notification.data.message}</p>
                            <small class="text-muted small">${new Date(notification.created_at).toLocaleDateString()}</small>
                        </div>
                    </a>
                    <button class="btn btn-sm btn-link p-0 mt-1 mark-as-read" data-id="${notification.id}">Mark as read</button>
                </div>
            `;
        } else {
            notificationsHtml += `
                <a href="${url}" class="list-group-item list-group-item-action list-group-item-light">
                    <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1 small text-muted">${notification.data.message}</p>
                        <small class="text-muted small">${new Date(notification.created_at).toLocaleDateString()}</small>
                    </div>
                </a>
            `;
        }
    });

    element.innerHTML = notificationsHtml;
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
        await fetchNotifications();
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
        await fetchNotifications();
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    }
}

function getApiToken() {
    return localStorage.getItem('auth_token');
}
