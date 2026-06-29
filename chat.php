<?php
session_start();
if (!isset($_SESSION['id_pengguna'])) {
    header('Location: login.php');
    exit;
}
$activeTokoId = isset($_GET['toko_id']) ? (int)$_GET['toko_id'] : 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - LokalThrift</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background: #eef5fc;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar (simple) */
        .top-nav {
            background: white;
            padding: 15px 24px;
            border-bottom: 1px solid #e0ecf8;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .top-nav a {
            text-decoration: none;
            color: #2a85ff;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .top-nav a:hover {
            opacity: 0.8;
        }

        /* Layout */
        .chat-container {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        /* Sidebar */
        .chat-sidebar {
            width: 320px;
            background: white;
            border-right: 1px solid #e0ecf8;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 20px;
            font-size: 18px;
            font-weight: 800;
            color: #0d1c2e;
            border-bottom: 1px solid #e0ecf8;
        }

        .contact-list {
            flex: 1;
            overflow-y: auto;
        }

        .contact-item {
            padding: 16px 20px;
            display: flex;
            gap: 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f6fc;
            transition: 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .contact-item:hover {
            background: #fafcff;
        }

        .contact-item.active {
            background: #eef5fc;
        }

        .contact-avatar {
            width: 46px;
            height: 46px;
            background: #e0ecf8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2a85ff;
            font-size: 20px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .contact-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .contact-info {
            flex: 1;
            overflow: hidden;
        }

        .contact-name {
            font-size: 14px;
            font-weight: 700;
            color: #0d1c2e;
            margin-bottom: 4px;
            display: flex;
            justify-content: space-between;
        }

        .contact-time {
            font-size: 11px;
            color: #8fa3b8;
            font-weight: 400;
        }

        .contact-msg {
            font-size: 12px;
            color: #8fa3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge-unread {
            background: #2a85ff;
            color: white;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 10px;
            float: right;
        }

        /* Main Chat */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f4f9fd;
        }

        .chat-header {
            padding: 15px 24px;
            background: white;
            border-bottom: 1px solid #e0ecf8;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .chat-header .name {
            font-size: 16px;
            font-weight: 700;
            color: #0d1c2e;
        }

        .chat-box {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .msg-wrap {
            display: flex;
            flex-direction: column;
            max-width: 70%;
        }

        .msg-wrap.left {
            align-self: flex-start;
        }

        .msg-wrap.right {
            align-self: flex-end;
        }

        .msg-bubble {
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.4;
        }

        .msg-wrap.left .msg-bubble {
            background: white;
            color: #0d1c2e;
            border: 1px solid #e0ecf8;
            border-bottom-left-radius: 4px;
        }

        .msg-wrap.right .msg-bubble {
            background: #2a85ff;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .msg-time {
            font-size: 11px;
            margin-top: 4px;
            color: #8fa3b8;
        }

        .msg-wrap.right .msg-time {
            text-align: right;
        }

        /* Chat Input */
        .chat-input-area {
            padding: 20px 24px;
            background: white;
            border-top: 1px solid #e0ecf8;
            display: flex;
            gap: 12px;
        }

        .chat-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #d4e3f3;
            border-radius: 12px;
            font-size: 14px;
            outline: none;
            resize: none;
            font-family: inherit;
        }

        .chat-input:focus {
            border-color: #2a85ff;
        }

        .btn-send {
            background: #2a85ff;
            color: white;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            flex-shrink: 0;
        }

        .btn-send:hover {
            opacity: 0.9;
        }

        .empty-chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #8fa3b8;
            gap: 16px;
        }

        .empty-chat i {
            font-size: 48px;
            color: #d4e3f3;
        }
    </style>
</head>

<body>

    <div class="top-nav">
        <a href="home.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        <span style="font-weight:700; color:#0d1c2e; margin-left:20px; font-size:18px;">Pesan Anda</span>
    </div>

    <div class="chat-container">
        <!-- Sidebar -->
        <div class="chat-sidebar">
            <div class="sidebar-header">Kontak Chat</div>
            <div class="contact-list" id="contact-list">
                <!-- AJAX Loaded -->
            </div>
        </div>

        <!-- Main -->
        <div class="chat-main" id="chat-main">
            <div class="empty-chat">
                <i class="fa-regular fa-comments"></i>
                <h3>Pilih chat untuk mulai membalas</h3>
            </div>
        </div>
    </div>

    <script>
        let activePartnerId = <?= $activeTokoId ?>;
        let activePartnerName = '';
        let activePartnerImg = '';
        let autoScroll = true;

        // INIT
        if (activePartnerId > 0) {
            // If opened with toko_id, assume we want to chat with them immediately.
            // We will render a temp header, wait for contacts to load.
            activePartnerName = 'Toko';
            renderChatArea();
        }

        fetchContacts();
        setInterval(fetchContacts, 3000);
        if (activePartnerId > 0) {
            setInterval(fetchMessages, 2000);
        }

        function fetchContacts() {
            fetch('api_chat.php?action=get_contacts')
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        renderContacts(res.data);

                        // If activePartnerId is set but we don't have their name yet, try to find it
                        if (activePartnerId > 0 && activePartnerName === 'Toko') {
                            const partner = res.data.find(c => c.id == activePartnerId);
                            if (partner) {
                                activePartnerName = partner.nama;
                                activePartnerImg = partner.foto;
                                document.getElementById('chat-title').innerText = partner.nama;
                                if (partner.foto) document.getElementById('chat-avatar').innerHTML = `<img src="${partner.foto}">`;
                            }
                        }
                    }
                });
        }

        function renderContacts(contacts) {
            const list = document.getElementById('contact-list');
            if (contacts.length === 0) {
                list.innerHTML = `<div style="padding:20px; text-align:center; color:#8fa3b8; font-size:13px;">Belum ada riwayat pesan</div>`;
                return;
            }

            list.innerHTML = contacts.map(c => `
        <div class="contact-item ${c.id == activePartnerId ? 'active' : ''}" onclick="openChat(${c.id}, '${c.nama.replace(/'/g, "\\'")}', '${c.foto}')">
            <div class="contact-avatar">
                ${c.foto ? `<img src="${c.foto}">` : `<i class="fa-solid fa-store"></i>`}
            </div>
            <div class="contact-info">
                <div class="contact-name">
                    ${c.nama}
                    <span class="contact-time">${c.last_time || ''}</span>
                </div>
                <div class="contact-msg">
                    ${c.last_msg || 'Mulai percakapan...'}
                    ${c.unread > 0 ? `<span class="badge-unread">${c.unread}</span>` : ''}
                </div>
            </div>
        </div>
    `).join('');
        }

        function openChat(id, nama, foto) {
            activePartnerId = id;
            activePartnerName = nama;
            activePartnerImg = foto;

            renderContacts([]); // Will be re-rendered by fetchContacts
            fetchContacts(); // Immediate fetch to update active state

            renderChatArea();
            fetchMessages();
        }

        function renderChatArea() {
            const main = document.getElementById('chat-main');
            main.innerHTML = `
        <div class="chat-header">
            <div class="contact-avatar" id="chat-avatar" style="width:36px;height:36px;font-size:16px;">
                ${activePartnerImg ? `<img src="${activePartnerImg}">` : `<i class="fa-solid fa-store"></i>`}
            </div>
            <div class="name" id="chat-title">${activePartnerName}</div>
        </div>
        <div class="chat-box" id="chat-box">
            <!-- Messages -->
        </div>
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="chat-input" placeholder="Tulis pesan..." onkeypress="handleEnter(event)">
            <button class="btn-send" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    `;

            const box = document.getElementById('chat-box');
            box.addEventListener('scroll', () => {
                // If user scrolls up, disable autoScroll
                if (box.scrollHeight - box.scrollTop - box.clientHeight > 50) {
                    autoScroll = false;
                } else {
                    autoScroll = true;
                }
            });
        }

        function fetchMessages() {
            if (!activePartnerId) return;

            fetch('api_chat.php?action=get_messages&id_partner=' + activePartnerId)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success') {
                        renderMessages(res.data);
                    }
                });
        }

        function renderMessages(messages) {
            const box = document.getElementById('chat-box');
            if (!box) return; // Chat not opened

            if (messages.length === 0) {
                box.innerHTML = `<div style="text-align:center; color:#8fa3b8; font-size:13px; margin-top:20px;">Belum ada pesan. Sapa penjual sekarang!</div>`;
                return;
            }

            box.innerHTML = messages.map(m => `
        <div class="msg-wrap ${m.sender === 'pembeli' ? 'right' : 'left'}">
            <div class="msg-bubble">${m.pesan}</div>
            <div class="msg-time">${m.waktu} ${m.sender === 'pembeli' ? (m.is_read ? '<i class="fa-solid fa-check-double" style="color:#2a85ff;margin-left:4px;"></i>' : '<i class="fa-solid fa-check" style="margin-left:4px;"></i>') : ''}</div>
        </div>
    `).join('');

            if (autoScroll) {
                box.scrollTop = box.scrollHeight;
            }
        }

        function handleEnter(e) {
            if (e.key === 'Enter') sendMessage();
        }

        function sendMessage() {
            const input = document.getElementById('chat-input');
            const msg = input.value.trim();
            if (!msg || !activePartnerId) return;

            input.value = '';

            // Optimistic UI update
            const box = document.getElementById('chat-box');
            if (box) {
                const time = new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
                box.insertAdjacentHTML('beforeend', `
            <div class="msg-wrap right">
                <div class="msg-bubble">${msg}</div>
                <div class="msg-time">${time} <i class="fa-solid fa-clock" style="margin-left:4px;"></i></div>
            </div>
        `);
                box.scrollTop = box.scrollHeight;
            }

            fetch('api_chat.php?action=send_message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_partner: activePartnerId,
                    pesan: msg
                })
            }).then(() => {
                fetchMessages();
                fetchContacts();
            });
        }
    </script>
</body>

</html>