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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Helvetica Neue', Arial, sans-serif;
        }

        body {
            background: #eef5fc;
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        /* BOTTOM NAVBAR (sama seperti halaman lain) */
        .navbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 10px 0 14px 0;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
            z-index: 999;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #777;
            text-decoration: none;
            flex: 1;
            gap: 4px;
        }

        .nav-item i {
            font-size: 20px;
            margin-bottom: 0;
            display: block;
        }

        .nav-item.active { color: #2a85ff; }
        .nav-item.nav-logout { color: #e53935; }
        .nav-item.nav-logout:hover { color: #c62828; }

        .sidebar-logo { display: none; }

        @media (min-width: 769px) {
            body { flex-direction: row; }

            .navbar {
                position: fixed;
                top: 0; left: 0; bottom: 0; right: auto;
                width: 160px; height: 100vh;
                flex-direction: column; justify-content: flex-start; align-items: stretch;
                padding: 20px 0 20px 0;
                border-top-left-radius: 0; border-top-right-radius: 0;
                border-right: 1px solid #e0ecf8; box-shadow: 4px 0 15px rgba(0, 0, 0, 0.05);
                gap: 4px;
            }

            .sidebar-logo {
                display: flex; align-items: center; justify-content: center; gap: 6px;
                font-size: 13px; font-weight: 800; color: #2a85ff;
                padding: 0 8px 18px 8px; border-bottom: 1px solid #e0ecf8;
                margin-bottom: 8px; text-align: center;
            }

            .nav-item {
                display: flex; flex-direction: column; align-items: center; justify-content: center;
                text-align: center; padding: 12px 8px; border-radius: 12px; margin: 2px 8px;
                font-size: 11px; font-weight: 600; flex: none;
                gap: 5px; transition: background 0.2s, color 0.2s;
            }

            .nav-item:hover { background: #eef5fc; color: #2a85ff; }
            .nav-item.active { background: #ddeeff; color: #2a85ff; }
            .nav-item.nav-logout { margin-top: auto; color: #e53935; }
            .nav-item.nav-logout:hover { background: #fff0f0; color: #c62828; }

            .nav-item i { font-size: 22px; display: block; margin-bottom: 0; width: auto; }

            .page-wrapper { margin-left: 160px; padding-bottom: 0 !important; }
        }

        /* WRAPPER konten di samping/atas navbar */
        .page-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            padding-bottom: 78px; /* ruang untuk bottom navbar mobile */
        }

        .chat-topbar {
            padding: 16px 24px;
            background: white;
            border-bottom: 1px solid #e0ecf8;
        }

        .chat-topbar .page-title {
            font-size: 18px;
            font-weight: 800;
            color: #0d1c2e;
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

    <div class="navbar">
        <div class="sidebar-logo">
            <img src="Logo.svg" alt="LokalThrift" style="width:140px; height:auto; display:block; margin:0 auto;">
        </div>
        <a href="home.php" class="nav-item">
            <i class="fa-solid fa-house"></i><span>Beranda</span>
        </a>
        <a href="keranjang.php" class="nav-item">
            <i class="fa-solid fa-cart-shopping"></i><span>Keranjang</span>
        </a>
        <a href="pesanan.php" class="nav-item">
            <i class="fa-solid fa-bag-shopping"></i><span>Pesanan</span>
        </a>
        <a href="cek_toko.php" class="nav-item">
            <i class="fa-solid fa-shop"></i><span>Toko</span>
        </a>
        <a href="akun.php" class="nav-item">
            <i class="fa-solid fa-user"></i><span>Akun</span>
        </a>
        <a href="chat.php" class="nav-item active">
            <i class="fa-solid fa-message"></i><span>Chat</span>
        </a>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin/dashboard.php" class="nav-item">
                <i class="fa-solid fa-chart-pie"></i><span>Admin</span>
            </a>
        <?php endif; ?>
        <a href="logout.php" class="nav-item nav-logout">
            <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
        </a>
    </div>

    <div class="page-wrapper">
        <div class="chat-topbar">
            <div class="page-title">Pesan Anda</div>
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
    </div><!-- /.page-wrapper -->

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