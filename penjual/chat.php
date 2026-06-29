<?php
require 'guard.php';
$pageTitle = 'Chat Pelanggan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $pageTitle ?> - Seller LokalThrift</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Reset & Base */
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body { background-color: #f8fafc; color: #1e293b; display: flex; height: 100vh; overflow: hidden; }

    /* Layout */
    .main-content { flex: 1; display: flex; flex-direction: column; overflow: hidden; background: white; margin: 24px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
    
    .chat-container { display:flex; flex:1; overflow:hidden; }
    
    /* Sidebar Chat */
    .chat-sidebar { width: 350px; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column; background: #f8fafc; }
    .sidebar-header { padding: 20px; font-size: 18px; font-weight: 700; color: #0f172a; border-bottom: 1px solid #e2e8f0; background: white; }
    .contact-list { flex: 1; overflow-y: auto; }
    
    .contact-item { padding: 16px 20px; display: flex; gap: 12px; cursor: pointer; border-bottom: 1px solid #e2e8f0; transition: 0.2s; background: white; }
    .contact-item:hover { background: #f1f5f9; }
    .contact-item.active { background: #e0f2fe; border-left: 4px solid #0ea5e9; padding-left: 16px; }
    
    .contact-avatar { width: 48px; height: 48px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 20px; flex-shrink: 0; overflow: hidden; }
    .contact-info { flex: 1; overflow: hidden; }
    .contact-name { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px; display: flex; justify-content: space-between; }
    .contact-time { font-size: 11px; color: #64748b; font-weight: 400; }
    .contact-msg { font-size: 13px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .badge-unread { background: #0ea5e9; color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px; float: right; }
    
    /* Main Chat */
    .chat-main { flex: 1; display: flex; flex-direction: column; background: white; }
    .chat-header { padding: 16px 24px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 16px; }
    .chat-header .name { font-size: 18px; font-weight: 700; color: #0f172a; }
    
    .chat-box { flex: 1; padding: 24px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; background: #f8fafc; }
    
    .msg-wrap { display: flex; flex-direction: column; max-width: 75%; }
    .msg-wrap.left { align-self: flex-start; }
    .msg-wrap.right { align-self: flex-end; }
    
    .msg-bubble { padding: 12px 16px; border-radius: 16px; font-size: 14px; line-height: 1.5; }
    .msg-wrap.left .msg-bubble { background: white; color: #0f172a; border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .msg-wrap.right .msg-bubble { background: #0ea5e9; color: white; border-bottom-right-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    
    .msg-time { font-size: 11px; margin-top: 6px; color: #64748b; }
    .msg-wrap.right .msg-time { text-align: right; }
    
    /* Input Area */
    .chat-input-area { padding: 20px 24px; background: white; border-top: 1px solid #e2e8f0; display: flex; gap: 16px; }
    .chat-input { flex: 1; padding: 14px 20px; border: 1px solid #cbd5e1; border-radius: 24px; font-size: 14px; outline: none; transition: 0.2s; }
    .chat-input:focus { border-color: #0ea5e9; box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1); }
    .btn-send { background: #0ea5e9; color: white; border: none; width: 48px; height: 48px; border-radius: 50%; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: 0.2s; flex-shrink: 0; }
    .btn-send:hover { background: #0284c7; transform: scale(1.05); }
    
    .empty-chat { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; gap: 16px; }
    .empty-chat i { font-size: 64px; color: #e2e8f0; }
    
  </style>
</head>
<body>

<!-- SIDEBAR PENJUAL -->
<?php include 'navbar.php'; ?>

<!-- KONTEN UTAMA -->
<div class="main-content">
  <div class="chat-container">
    <!-- Sidebar -->
    <div class="chat-sidebar">
      <div class="sidebar-header">Pesan Pelanggan</div>
      <div class="contact-list" id="contact-list">
        <!-- AJAX Loaded -->
      </div>
    </div>
    
    <!-- Main -->
    <div class="chat-main" id="chat-main">
      <div class="empty-chat">
        <i class="fa-regular fa-comments"></i>
        <h3>Pilih chat untuk melihat pesan</h3>
      </div>
    </div>
  </div>
</div>

<script>
let activePartnerId = 0;
let activePartnerName = '';
let autoScroll = true;

fetchContacts();
setInterval(fetchContacts, 3000);

function fetchContacts() {
    fetch('../api_chat.php?action=get_contacts')
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') {
            renderContacts(res.data);
        }
    });
}

function renderContacts(contacts) {
    const list = document.getElementById('contact-list');
    if (contacts.length === 0) {
        list.innerHTML = `<div style="padding:40px 20px; text-align:center; color:#94a3b8; font-size:14px;">Belum ada pesan dari pelanggan.</div>`;
        return;
    }
    
    list.innerHTML = contacts.map(c => `
        <div class="contact-item ${c.id == activePartnerId ? 'active' : ''}" onclick="openChat(${c.id}, '${c.nama.replace(/'/g, "\\'")}')">
            <div class="contact-avatar">
                <i class="fa-solid fa-user"></i>
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

function openChat(id, nama) {
    activePartnerId = id;
    activePartnerName = nama;
    
    renderContacts([]); 
    fetchContacts(); 
    
    renderChatArea();
    fetchMessages();
    
    // Set interval for messages
    if(window.msgInterval) clearInterval(window.msgInterval);
    window.msgInterval = setInterval(fetchMessages, 2000);
}

function renderChatArea() {
    const main = document.getElementById('chat-main');
    main.innerHTML = `
        <div class="chat-header">
            <div class="contact-avatar" style="width:40px;height:40px;font-size:18px;">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="name">${activePartnerName}</div>
        </div>
        <div class="chat-box" id="chat-box"></div>
        <div class="chat-input-area">
            <input type="text" class="chat-input" id="chat-input" placeholder="Tulis balasan..." onkeypress="handleEnter(event)">
            <button class="btn-send" onclick="sendMessage()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    `;
    
    const box = document.getElementById('chat-box');
    box.addEventListener('scroll', () => {
        if (box.scrollHeight - box.scrollTop - box.clientHeight > 50) {
            autoScroll = false;
        } else {
            autoScroll = true;
        }
    });
}

function fetchMessages() {
    if (!activePartnerId) return;
    
    fetch('../api_chat.php?action=get_messages&id_partner=' + activePartnerId)
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') {
            renderMessages(res.data);
        }
    });
}

function renderMessages(messages) {
    const box = document.getElementById('chat-box');
    if (!box) return; 
    
    if (messages.length === 0) {
        box.innerHTML = `<div style="text-align:center; color:#94a3b8; font-size:14px; margin-top:20px;">Kirim pesan pertama Anda...</div>`;
        return;
    }
    
    box.innerHTML = messages.map(m => `
        <div class="msg-wrap ${m.sender === 'penjual' ? 'right' : 'left'}">
            <div class="msg-bubble">${m.pesan}</div>
            <div class="msg-time">${m.waktu} ${m.sender === 'penjual' ? (m.is_read ? '<i class="fa-solid fa-check-double" style="color:#0ea5e9;margin-left:4px;"></i>' : '<i class="fa-solid fa-check" style="margin-left:4px;"></i>') : ''}</div>
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
    
    const box = document.getElementById('chat-box');
    if(box) {
        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        box.insertAdjacentHTML('beforeend', `
            <div class="msg-wrap right">
                <div class="msg-bubble">${msg}</div>
                <div class="msg-time">${time} <i class="fa-solid fa-clock" style="margin-left:4px;"></i></div>
            </div>
        `);
        box.scrollTop = box.scrollHeight;
    }
    
    fetch('../api_chat.php?action=send_message', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
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
